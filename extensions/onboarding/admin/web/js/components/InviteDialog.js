import React, { useEffect, useState } from 'react';
import {
  Alert, Col, Form, Input, Modal, Row, Select, Typography, message,
} from 'antd';
import { t } from '../../../../../../web/api/i18n';
import { errorMessage } from '../shared/api';

const { Text, Paragraph } = Typography;

const filterOption = (input, option) => String(option.label || '').toLowerCase().includes(input.toLowerCase());

/**
 * Invite a new hire (their basics, start date and the workflow) or, on the workforce
 * track, an existing employee of a company that is starting to use IceHrm (no start
 * date; their joined date instead). Creates the onboarding and sends the "set up your
 * account" invitation.
 */
export default function InviteDialog({
  api, open, onClose, onInvited, presetWorkflow, track = 'new_hire',
}) {
  const workforce = track === 'workforce';
  const [form] = Form.useForm();
  const [lookups, setLookups] = useState(null);
  const [choices, setChoices] = useState([]);
  const [busy, setBusy] = useState(false);
  const [errors, setErrors] = useState({});
  const [done, setDone] = useState(null);

  useEffect(() => {
    if (!open) return;
    setErrors({});
    setDone(null);
    form.resetFields();
    if (presetWorkflow) form.setFieldsValue({ workflow: presetWorkflow });
    api.get('onboarding/lookups').then(setLookups).catch(() => setLookups({}));
    api.get('onboarding/workflow-choices', { track }).then((c) => {
      const list = c || [];
      setChoices(list);
      // Preselect when there is only one choice, or the standard workflow for existing
      // staff (Workforce onboarding), as the Invite your team grid does.
      if (!presetWorkflow && !form.getFieldValue('workflow')) {
        const standard = list.filter((x) => x.group === 'standard');
        let pick = null;
        if (list.length === 1) pick = list[0].value;
        else if (workforce && standard.length === 1) pick = standard[0].value;
        if (pick) form.setFieldsValue({ workflow: pick });
      }
    }).catch(() => setChoices([]));
  }, [open]);

  const workflowOptions = [
    {
      label: t('Standard workflows'),
      options: choices.filter((c) => c.group === 'standard').map((c) => ({ value: c.value, label: c.label })),
    },
    {
      label: t('Custom workflows'),
      options: choices.filter((c) => c.group === 'custom').map((c) => ({ value: c.value, label: c.label })),
    },
  ].filter((g) => g.options.length > 0);

  const submit = () => {
    form.validateFields().then((values) => {
      setBusy(true);
      setErrors({});
      api.post('onboarding/hires', { ...values, track })
        .then((res) => {
          if (!res || !res.ok) {
            setErrors((res && res.errors) || {});
            return;
          }
          setDone(res);
          if (onInvited) onInvited(res.id);
        })
        .catch((e) => message.error(errorMessage(e, t('The invitation could not be sent'))))
        .finally(() => setBusy(false));
    }).catch(() => {});
  };

  const field = (name) => (errors[name] ? { validateStatus: 'error', help: errors[name] } : {});
  const l = lookups || {};

  if (done) {
    return (
      <Modal open={open} title={done.sent ? t('Invitation sent') : t('Invitation email not sent')} onCancel={onClose} onOk={onClose} okText={t('Done')} cancelButtonProps={{ style: { display: 'none' } }}>
        {done.sent ? (
          <Paragraph>
            {workforce
              ? t('We emailed the employee a link to set up their account. You can also share the link yourself; it is shown only once.')
              : t('We emailed the new hire a link to set up their account. You can also share the link yourself; it is shown only once.')}
          </Paragraph>
        ) : (
          <Alert
            type="warning"
            showIcon
            style={{ marginBottom: 12 }}
            message={workforce
              ? t('The email could not be sent. Share this link with the employee yourself; it is shown only once.')
              : t('The email could not be sent. Share this link with the new hire yourself; it is shown only once.')}
          />
        )}
        <Paragraph copyable code style={{ wordBreak: 'break-all' }}>{done.url}</Paragraph>
      </Modal>
    );
  }

  return (
    <Modal
      open={open}
      title={workforce ? t('Invite an employee') : t('Invite a new hire')}
      onCancel={onClose}
      onOk={submit}
      okText={t('Send invitation')}
      confirmLoading={busy}
      width={720}
      destroyOnClose
    >
      <Text type="secondary">
        {workforce
          ? t('The employee gets an email to set up their account, then sees a short list of details to check and complete.')
          : t('The new hire gets an email to set up their account, then sees their onboarding checklist. They stay out of the directory, payroll and leave until their start date.')}
      </Text>
      {errors._ ? <Alert type="error" showIcon style={{ marginTop: 12 }} message={errors._} /> : null}
      <Form form={form} layout="vertical" style={{ marginTop: 16 }} requiredMark="optional">
        <Row gutter={12}>
          <Col xs={24} sm={12}>
            <Form.Item name="first_name" label={t('First name')} rules={[{ required: true, message: t('Enter the first name.') }]} {...field('first_name')}>
              <Input maxLength={100} />
            </Form.Item>
          </Col>
          <Col xs={24} sm={12}>
            <Form.Item name="last_name" label={t('Last name')} rules={[{ required: true, message: t('Enter the last name.') }]} {...field('last_name')}>
              <Input maxLength={100} />
            </Form.Item>
          </Col>
          <Col xs={24} sm={12}>
            <Form.Item name="email" label={workforce ? t('Email') : t('Personal email')} rules={[{ required: true, type: 'email', message: t('Enter a valid email address.') }]} {...field('email')}>
              <Input maxLength={100} />
            </Form.Item>
          </Col>
          {workforce ? (
            <Col xs={24} sm={12}>
              <Form.Item name="joined_date" label={t('Joined date')} {...field('joined_date')}>
                <Input type="date" />
              </Form.Item>
            </Col>
          ) : (
            <Col xs={24} sm={12}>
              <Form.Item name="start_date" label={t('Start date')} rules={[{ required: true, message: t('Enter the start date.') }]} {...field('start_date')}>
                <Input type="date" />
              </Form.Item>
            </Col>
          )}
          <Col xs={24}>
            <Form.Item name="workflow" label={t('Workflow')} rules={[{ required: true, message: t('Choose a workflow.') }]} {...field('workflow')}>
              <Select options={workflowOptions} showSearch filterOption={filterOption} placeholder={t('Choose a workflow')} />
            </Form.Item>
          </Col>
          <Col xs={24} sm={12}>
            <Form.Item name="job_title" label={t('Job title')} {...field('job_title')}>
              <Select allowClear showSearch filterOption={filterOption} options={l.job_titles || []} loading={!lookups} />
            </Form.Item>
          </Col>
          <Col xs={24} sm={12}>
            <Form.Item name="department" label={t('Department')} {...field('department')}>
              <Select allowClear showSearch filterOption={filterOption} options={l.departments || []} loading={!lookups} />
            </Form.Item>
          </Col>
          <Col xs={24} sm={12}>
            <Form.Item
              name="supervisor"
              label={t('Manager')}
              extra={workforce ? t('Leave empty to be their manager until it is set in the onboarding.') : t('Gets the manager tasks of the workflow.')}
              {...field('supervisor')}
            >
              <Select allowClear showSearch filterOption={filterOption} options={l.people || []} loading={!lookups} />
            </Form.Item>
          </Col>
          {workforce ? null : (
            <Col xs={24} sm={12}>
              <Form.Item name="buddy" label={t('Buddy')} extra={t('A colleague who helps in the first weeks.')} {...field('buddy')}>
                <Select allowClear showSearch filterOption={filterOption} options={l.people || []} loading={!lookups} />
              </Form.Item>
            </Col>
          )}
          <Col xs={24} sm={12}>
            <Form.Item name="employment_status" label={t('Employment status')} {...field('employment_status')}>
              <Select allowClear showSearch filterOption={filterOption} options={l.employment_statuses || []} loading={!lookups} />
            </Form.Item>
          </Col>
          <Col xs={24} sm={12}>
            <Form.Item name="country" label={t('Country')}>
              <Select allowClear showSearch filterOption={filterOption} options={l.countries || []} loading={!lookups} />
            </Form.Item>
          </Col>
          <Col xs={24} sm={12}>
            <Form.Item name="user_level" label={t('Access level')} initialValue="Employee">
              <Select options={[
                { value: 'Employee', label: t('Employee') },
                { value: 'Manager', label: t('Manager') },
                { value: 'Admin', label: t('Admin') },
              ]}
              />
            </Form.Item>
          </Col>
          <Col xs={24}>
            <Form.Item name="welcome_message" label={t('Welcome message')} extra={t('Shown on the invitation and at the top of the checklist. Leave empty to use the workflow\'s message.')}>
              <Input.TextArea autoSize={{ minRows: 2, maxRows: 6 }} maxLength={5000} />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </Modal>
  );
}
