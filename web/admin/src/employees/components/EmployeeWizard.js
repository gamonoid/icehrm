/*
 Employee wizard — a re-skinned step modal for adding/editing employees,
 modelled on the Leave Type / Candidate wizards: icon steps, a heading + blurb
 per step and context-aware actions. It deliberately extends
 IceStepForm/IceStepFormModal so every field keeps the standard machinery
 (remote selects, datagroups, dynamic field-name mappings, custom fields,
 validation, jump-to-invalid-step and adapter.add persistence).
*/
import React from 'react';
import {
  Button, Modal, Space, Steps, Typography, theme,
} from 'antd';
import {
  IdcardOutlined, SafetyCertificateOutlined, ApartmentOutlined, MailOutlined,
  TeamOutlined, AppstoreOutlined,
} from '@ant-design/icons';
import IceStepForm from '../../../../components/IceStepForm';
import IceStepFormModal from '../../../../components/IceStepFromModal';
import { t } from '../../../../api/i18n';

const { Title, Text } = Typography;

// Keyed by the step titles from EmployeeAdapter.getMappedFields().
// A function (not a constant) so the texts are translated at render time.
const stepMeta = () => ({
  Personal: {
    icon: <IdcardOutlined />,
    heading: t('Who are they?'),
    blurb: t('Name, date of birth and other personal details.'),
  },
  Identification: {
    icon: <SafetyCertificateOutlined />,
    heading: t('Identification'),
    blurb: t('Tax, insurance and identity document numbers.'),
  },
  Work: {
    icon: <ApartmentOutlined />,
    heading: t('Role & employment'),
    blurb: t('Where they sit in the company and when they joined.'),
  },
  Contact: {
    icon: <MailOutlined />,
    heading: t('How to reach them'),
    blurb: t('Address, phone numbers and email addresses.'),
  },
  Report: {
    icon: <TeamOutlined />,
    heading: t('Reporting lines'),
    blurb: t('Their manager, leave approvers and internal notes.'),
  },
  Other: {
    icon: <AppstoreOutlined />,
    heading: t('Additional information'),
    blurb: t('Custom fields defined by your company.'),
  },
});

// The action row stays pinned to the bottom of the modal. Most steps are taller
// than the window, and Save / Next used to sit below the fold, out of reach until
// the modal was scrolled to the end.
const StickyActions = ({ children }) => {
  const { token } = theme.useToken();
  return (
    <div style={{
      position: 'sticky',
      bottom: 0,
      zIndex: 5,
      display: 'flex',
      justifyContent: 'space-between',
      marginTop: 16,
      marginInline: -24,
      marginBottom: -20,
      padding: '12px 24px 20px',
      background: token.colorBgElevated,
      borderTop: `1px solid ${token.colorSplit}`,
      borderBottomLeftRadius: token.borderRadiusLG,
      borderBottomRightRadius: token.borderRadiusLG,
    }}
    >
      {children}
    </div>
  );
};

class EmployeeStepForm extends IceStepForm {
  render() {
    const { adapter } = this.props;
    const { current, steps } = this.state;
    const gt = (s) => adapter.gt(s);
    const meta = stepMeta()[steps[current].title] || {};
    const isLast = current === steps.length - 1;
    const editing = !!(adapter.currentElement && adapter.currentElement.id);

    return (
      <>
        <Steps
          size="small"
          current={current}
          onChange={(c) => this.moveToStep(c)}
          items={steps.map((item) => ({
            title: gt(item.title),
            icon: (stepMeta()[item.title] || {}).icon,
          }))}
          style={{ marginBottom: 20 }}
        />

        <div style={{ marginBottom: 12 }}>
          <Title level={5} style={{ marginBottom: 2 }}>{meta.heading || gt(steps[current].description || steps[current].title)}</Title>
          {meta.blurb && <Text type="secondary">{meta.blurb}</Text>}
        </div>

        <div className="steps-content">
          {steps.map((item, index) => (
            <div key={item.title} style={{ display: index === current ? 'block' : 'none' }}>
              {item.content}
            </div>
          ))}
        </div>

        <StickyActions>
          <div>
            {current > 0 && <Button onClick={() => this.prev()}>{gt('Back')}</Button>}
          </div>
          <Space>
            <Button onClick={() => this.props.closeModal()}>{gt('Cancel')}</Button>
            {!isLast && (
              // While editing, Save sits beside Next on every step so a change
              // can be saved without walking to the end — Next drops to a
              // secondary button there, since saving is the primary intent.
              <Button type={editing ? 'default' : 'primary'} onClick={() => this.next()}>{gt('Next')}</Button>
            )}
            {(isLast || editing) && (
              <Button type="primary" loading={this.state.loading} onClick={() => this.saveData()}>
                {editing ? gt('Save changes') : gt('Create employee')}
              </Button>
            )}
          </Space>
        </StickyActions>
      </>
    );
  }
}

class EmployeeWizardModal extends IceStepFormModal {
  render() {
    const { fields, adapter } = this.props;
    const editing = !!(adapter.currentElement && adapter.currentElement.id);
    return (
      <Modal
        open={this.state.visible}
        title={adapter.gt(editing ? 'Edit Employee' : 'New Employee')}
        maskClosable={false}
        width={860}
        footer={[]}
        onCancel={() => {
          this.closeModal();
        }}
      >
        <EmployeeStepForm
          ref={this.iceFormReference}
          adapter={adapter}
          fields={fields}
          closeModal={() => { this.closeModal(); }}
          layout="vertical"
          // Narrow inputs flow two per row (wide fields like the notes
          // datagroup still span the full width) to keep the modal short.
          twoColumnLayout
        />
      </Modal>
    );
  }
}

export default EmployeeWizardModal;
