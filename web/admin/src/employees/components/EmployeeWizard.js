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
  Button, Modal, Space, Steps, Typography,
} from 'antd';
import {
  IdcardOutlined, SafetyCertificateOutlined, ApartmentOutlined, MailOutlined,
  TeamOutlined, AppstoreOutlined,
} from '@ant-design/icons';
import IceStepForm from '../../../../components/IceStepForm';
import IceStepFormModal from '../../../../components/IceStepFromModal';

const { Title, Text } = Typography;

// Keyed by the step titles from EmployeeAdapter.getMappedFields().
const STEP_META = {
  Personal: {
    icon: <IdcardOutlined />,
    heading: 'Who are they?',
    blurb: 'Name, date of birth and other personal details.',
  },
  Identification: {
    icon: <SafetyCertificateOutlined />,
    heading: 'Identification',
    blurb: 'Tax, insurance and identity document numbers.',
  },
  Work: {
    icon: <ApartmentOutlined />,
    heading: 'Role & employment',
    blurb: 'Where they sit in the company and when they joined.',
  },
  Contact: {
    icon: <MailOutlined />,
    heading: 'How to reach them',
    blurb: 'Address, phone numbers and email addresses.',
  },
  Report: {
    icon: <TeamOutlined />,
    heading: 'Reporting lines',
    blurb: 'Their manager, leave approvers and internal notes.',
  },
  Other: {
    icon: <AppstoreOutlined />,
    heading: 'Additional information',
    blurb: 'Custom fields defined by your company.',
  },
};

class EmployeeStepForm extends IceStepForm {
  render() {
    const { adapter } = this.props;
    const { current, steps } = this.state;
    const gt = (s) => adapter.gt(s);
    const meta = STEP_META[steps[current].title] || {};
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
            icon: (STEP_META[item.title] || {}).icon,
          }))}
          style={{ marginBottom: 20 }}
        />

        <div style={{ marginBottom: 12 }}>
          <Title level={5} style={{ marginBottom: 2 }}>{gt(meta.heading || steps[current].description || steps[current].title)}</Title>
          {meta.blurb && <Text type="secondary">{gt(meta.blurb)}</Text>}
        </div>

        <div className="steps-content">
          {steps.map((item, index) => (
            <div key={item.title} style={{ display: index === current ? 'block' : 'none' }}>
              {item.content}
            </div>
          ))}
        </div>

        <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 16 }}>
          <div>
            {current > 0 && <Button onClick={() => this.prev()}>{gt('Back')}</Button>}
          </div>
          <Space>
            <Button onClick={() => this.props.closeModal()}>{gt('Cancel')}</Button>
            {!isLast && (
              <Button type="primary" onClick={() => this.next()}>{gt('Next')}</Button>
            )}
            {isLast && (
              <Button type="primary" loading={this.state.loading} onClick={() => this.saveData()}>
                {editing ? gt('Save changes') : gt('Create employee')}
              </Button>
            )}
          </Space>
        </div>
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
