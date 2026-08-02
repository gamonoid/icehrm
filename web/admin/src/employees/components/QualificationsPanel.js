/*
 QualificationsPanel — the redesigned Qualifications tab of the employee
 profile. Replaces the four bare tag columns with a responsive 2×2 grid of
 cards that surface the full data each record carries: skill details,
 education/certification institutes and periods, and per-language proficiency
 levels. Loading skeletons, empty states and count badges included.
*/
import React from 'react';
import {
  Card, Col, Empty, Row, Skeleton, Space, Tag, Tooltip, Typography,
} from 'antd';
import {
  ToolOutlined, ReadOutlined, SafetyCertificateOutlined, GlobalOutlined,
} from '@ant-design/icons';

const { Text } = Typography;

// Proficiency enum -> level (1–5) for the compact language meters.
const PROFICIENCY_LEVELS = {
  'Elementary Proficiency': 1,
  'Limited Working Proficiency': 2,
  'Professional Working Proficiency': 3,
  'Full Professional Proficiency': 4,
  'Native or Bilingual Proficiency': 5,
};

const displayOf = (v) => (v && typeof v === 'object' ? v.display : v) || '';

const yearOf = (d) => (d && d !== '0000-00-00' ? String(d).slice(0, 4) : '');

const periodOf = (start, end) => {
  const from = yearOf(start);
  const to = yearOf(end);
  if (!from && !to) return '';
  return `${from || '…'} – ${to || 'Present'}`;
};

// Five-dot meter for one language dimension (e.g. Reading at level 3/5).
function LevelDots({ label, value }) {
  const level = PROFICIENCY_LEVELS[value] || 0;
  if (!level) return null;
  return (
    <Tooltip title={`${label}: ${value}`}>
      <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4, marginRight: 12 }}>
        <Text type="secondary" style={{ fontSize: 12 }}>{label}</Text>
        <span style={{ letterSpacing: 1, fontSize: 11, color: '#722ed1' }}>
          {'●'.repeat(level)}
          <span style={{ opacity: 0.25 }}>{'●'.repeat(5 - level)}</span>
        </span>
      </span>
    </Tooltip>
  );
}

class QualificationsPanel extends React.Component {
  state = {
    loading: true,
    skills: [],
    educations: [],
    certifications: [],
    languages: [],
  };

  componentDidMount() {
    this.fetch();
  }

  componentDidUpdate(prevProps) {
    if (prevProps.employeeId !== this.props.employeeId) {
      this.fetch();
    }
  }

  fetch() {
    const { apiClient, employeeId } = this.props;
    this.setState({ loading: true });
    Promise.all([
      apiClient.get(`employees/${employeeId}/skills`),
      apiClient.get(`employees/${employeeId}/educations`),
      apiClient.get(`employees/${employeeId}/certifications`),
      apiClient.get(`employees/${employeeId}/languages`),
    ]).then(([skills, educations, certifications, languages]) => {
      this.setState({
        loading: false,
        skills: skills.data.data || [],
        educations: educations.data.data || [],
        certifications: certifications.data.data || [],
        languages: languages.data.data || [],
      });
    }).catch(() => this.setState({ loading: false }));
  }

  card(title, icon, iconColor, count, extra, body) {
    const { gt } = this.props;
    return (
      <Card
        size="small"
        title={(
          <Space size={8}>
            {React.cloneElement(icon, { style: { color: iconColor } })}
            <span>{gt(title)}</span>
            <Tag style={{ marginLeft: 2, borderRadius: 10 }}>{count}</Tag>
          </Space>
        )}
        extra={extra}
        style={{ width: '100%', height: '100%' }}
        styles={{ body: { maxHeight: 260, overflowY: 'auto' } }}
      >
        {this.state.loading ? <Skeleton active paragraph={{ rows: 2 }} /> : body}
      </Card>
    );
  }

  empty(text) {
    return <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description={this.props.gt(text)} style={{ margin: '8px 0' }} />;
  }

  // One record row: strong title, optional secondary line, optional muted meta.
  rowItem(key, title, secondary, meta) {
    return (
      <div key={key} style={{ padding: '8px 0', borderBottom: '1px solid rgba(140,140,140,0.15)' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', gap: 8, alignItems: 'baseline' }}>
          <Text strong>{title}</Text>
          {meta ? <Text type="secondary" style={{ fontSize: 12, whiteSpace: 'nowrap' }}>{meta}</Text> : null}
        </div>
        {secondary ? (
          <Text type="secondary" style={{ fontSize: 12.5, display: 'block', marginTop: 2 }}>{secondary}</Text>
        ) : null}
      </div>
    );
  }

  render() {
    const { gt, editExtra } = this.props;
    const {
      skills, educations, certifications, languages,
    } = this.state;

    return (
      <Row gutter={[16, 16]} style={{ width: '100%', padding: '10px' }}>
        <Col xs={24} md={12}>
          {this.card('Skills', <ToolOutlined />, '#1677ff', skills.length, editExtra('tabEmployeeSkill'),
            skills.length === 0 ? this.empty('No skills recorded') : (
              <div>
                {skills.map((s) => this.rowItem(s.id, displayOf(s.skill_id), s.details, null))}
              </div>
            ))}
        </Col>
        <Col xs={24} md={12}>
          {this.card('Education', <ReadOutlined />, '#13c2c2', educations.length, editExtra('tabEmployeeEducation'),
            educations.length === 0 ? this.empty('No education recorded') : (
              <div>
                {educations.map((e) => this.rowItem(
                  e.id,
                  displayOf(e.education_id),
                  e.institute,
                  periodOf(e.date_start, e.date_end),
                ))}
              </div>
            ))}
        </Col>
        <Col xs={24} md={12}>
          {this.card('Certifications', <SafetyCertificateOutlined />, '#fa8c16', certifications.length, editExtra('tabEmployeeCertification'),
            certifications.length === 0 ? this.empty('No certifications recorded') : (
              <div>
                {certifications.map((c) => this.rowItem(
                  c.id,
                  displayOf(c.certification_id),
                  c.institute,
                  periodOf(c.date_start, c.date_end),
                ))}
              </div>
            ))}
        </Col>
        <Col xs={24} md={12}>
          {this.card('Languages', <GlobalOutlined />, '#722ed1', languages.length, editExtra('tabEmployeeLanguage'),
            languages.length === 0 ? this.empty('No languages recorded') : (
              <div>
                {languages.map((l) => (
                  <div key={l.id} style={{ padding: '8px 0', borderBottom: '1px solid rgba(140,140,140,0.15)' }}>
                    <Text strong>{displayOf(l.language_id)}</Text>
                    <div style={{ marginTop: 4, display: 'flex', flexWrap: 'wrap', rowGap: 4 }}>
                      <LevelDots label={gt('Read')} value={l.reading} />
                      <LevelDots label={gt('Speak')} value={l.speaking} />
                      <LevelDots label={gt('Write')} value={l.writing} />
                      <LevelDots label={gt('Understand')} value={l.understanding} />
                    </div>
                  </div>
                ))}
              </div>
            ))}
        </Col>
      </Row>
    );
  }
}

export default QualificationsPanel;
