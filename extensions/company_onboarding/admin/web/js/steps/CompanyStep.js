import React, { useEffect, useState } from 'react';
import {
  Col, Form, Input, InputNumber, Row, Select, Skeleton, Switch, message,
} from 'antd';
import { t } from '../../../../../../web/api/i18n';
import { errorMessage } from '../api';
import StepFooter from '../components/StepFooter';

function browserTimeZone() {
  try {
    return Intl.DateTimeFormat().resolvedOptions().timeZone || null;
  } catch (e) {
    return null;
  }
}

const filterOption = (input, option) => String(option.label).toLowerCase().includes(input.toLowerCase());

/** Step 1 (required): company name, country, time zone and a few company settings. */
export default function CompanyStep({
  api, step, busy, onContinue, onSkip,
}) {
  const [values, setValues] = useState(null);
  const [lookups, setLookups] = useState({ countries: [], timezones: [] });
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    api.get('company-onboarding/lookups').then(setLookups).catch(() => {});
    api.get('company-onboarding/company')
      .then((c) => setValues({
        ...c,
        timezone: c.timezone || browserTimeZone(),
      }))
      .catch((e) => message.error(errorMessage(e, t('The company details could not be loaded'))));
  }, []);

  if (!values) return <Skeleton active />;

  const set = (patch) => setValues({ ...values, ...patch });
  const field = (name) => (errors[name] ? { validateStatus: 'error', help: errors[name] } : {});

  const save = () => {
    setSaving(true);
    setErrors({});
    api.post('company-onboarding/company', values)
      .then((res) => {
        if (!res || !res.ok) {
          setErrors((res && res.errors) || {});
          return;
        }
        message.success(t('Company details saved'));
        onContinue();
      })
      .catch((e) => message.error(errorMessage(e, t('The company details could not be saved'))))
      .finally(() => setSaving(false));
  };

  return (
    <Form layout="vertical" requiredMark="optional">
      <Row gutter={16}>
        <Col xs={24} md={12}>
          <Form.Item label={t('Company name')} required {...field('name')}>
            <Input value={values.name} onChange={(e) => set({ name: e.target.value })} maxLength={100} placeholder={t('e.g. Acme Ltd')} />
          </Form.Item>
        </Col>
        <Col xs={24} md={12}>
          <Form.Item label={t('Country')} required {...field('country')}>
            <Select
              showSearch
              value={values.country || undefined}
              options={lookups.countries}
              filterOption={filterOption}
              onChange={(v) => set({ country: v })}
              placeholder={t('Choose the country')}
            />
          </Form.Item>
        </Col>
        <Col xs={24} md={12}>
          <Form.Item label={t('Time zone')} required {...field('timezone')}>
            <Select
              showSearch
              value={values.timezone || undefined}
              options={(lookups.timezones || []).map((z) => ({ value: z, label: z }))}
              filterOption={filterOption}
              onChange={(v) => set({ timezone: v })}
              placeholder={t('Choose the time zone')}
            />
          </Form.Item>
        </Col>
        <Col xs={24} md={12}>
          <Form.Item label={t('Standard weekly hours')} {...field('weekly_hours')}>
            <InputNumber style={{ width: '100%' }} min={1} max={80} value={values.weekly_hours} onChange={(v) => set({ weekly_hours: v })} />
          </Form.Item>
        </Col>
        <Col xs={24} md={12}>
          <Form.Item label={t('Give employees a number automatically')}>
            <Switch checked={!!values.generate_numbers} onChange={(v) => set({ generate_numbers: v })} />
          </Form.Item>
        </Col>
        {values.generate_numbers ? (
          <Col xs={24} md={12}>
            <Form.Item label={t('Employee number prefix')} {...field('number_prefix')} extra={t('For example EMP- gives EMP-001, EMP-002 ...')}>
              <Input value={values.number_prefix} onChange={(e) => set({ number_prefix: e.target.value })} maxLength={10} />
            </Form.Item>
          </Col>
        ) : null}
      </Row>
      <StepFooter
        step={step}
        busy={busy || saving}
        onSkip={onSkip}
        onContinue={save}
        continueLabel={t('Save and continue')}
      />
    </Form>
  );
}
