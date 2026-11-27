import React, { useEffect, useState } from 'react';
import {
  Alert, Button, Card, Checkbox, Col, Drawer, Dropdown, Empty, Input, InputNumber, Modal, Row, Select, Skeleton, Space, Tag, Typography, message,
} from 'antd';
import {
  ArrowDownOutlined, ArrowUpOutlined, DeleteOutlined, PlusOutlined, SaveOutlined,
} from '@ant-design/icons';
import { t } from '../../../../../../web/api/i18n';
import { errorMessage } from '../shared/api';
import {
  NEW_HIRE_TYPES, PHASES, STEP_TYPES, dueLabel, ownerLabel, phaseLabel, typeHelp, typeLabel,
} from '../shared/constants';

const { Text } = Typography;

const filterOption = (input, option) => String(option.label || '').toLowerCase().includes(input.toLowerCase());

function phaseFor(offset) {
  const n = Number(offset) || 0;
  if (n < 0) return 'before';
  if (n === 0) return 'day1';
  return n <= 7 ? 'week1' : 'month1';
}

let seq = 0;
function newStep(type) {
  seq += 1;
  const forHire = NEW_HIRE_TYPES.includes(type);
  return {
    // Not Date.now(): the legacy Datejs library on the page makes it return a Date,
    // whose toString() takes a format string, so .toString(36) throws.
    key: `step_${new Date().getTime().toString(36)}_${seq}`,
    type,
    title: '',
    instructions: '',
    owner_type: forHire ? 'new_hire' : 'hr',
    owner_employee: null,
    due_offset_days: forHire ? -3 : -1,
    phase: forHire ? 'before' : 'before',
    required: true,
    needs_review: type === 'document',
    config: {},
  };
}

function StepEditor({
  step, index, count, options, people, onChange, onMove, onRemove, track,
}) {
  const set = (patch) => onChange({ ...step, ...patch });
  const setConfig = (patch) => set({ config: { ...(step.config || {}), ...patch } });
  const forHire = NEW_HIRE_TYPES.includes(step.type);
  const unavailable = options.available && options.available[step.type] === false;

  let config = null;
  switch (step.type) {
    case 'info':
    case 'acknowledge':
      config = (
        <>
          <Col xs={24}>
            <Text>{step.type === 'acknowledge' ? t('Text to acknowledge') : t('Text')}</Text>
            <Input.TextArea value={step.config.text || ''} onChange={(e) => setConfig({ text: e.target.value })} autoSize={{ minRows: 3, maxRows: 12 }} maxLength={20000} />
          </Col>
          <Col xs={24} sm={14}>
            <Text>{t('Link (optional)')}</Text>
            <Input value={step.config.link || ''} onChange={(e) => setConfig({ link: e.target.value })} placeholder="https://" maxLength={500} />
          </Col>
          <Col xs={24} sm={10}>
            <Text>{t('Link text')}</Text>
            <Input value={step.config.link_label || ''} onChange={(e) => setConfig({ link_label: e.target.value })} maxLength={120} />
          </Col>
        </>
      );
      break;
    case 'document':
      config = (
        <Col xs={24} sm={12}>
          <Text>{t('Document type')}</Text>
          <Select
            style={{ width: '100%' }}
            showSearch
            filterOption={filterOption}
            value={step.config.document_type || undefined}
            placeholder={step.config.document_name || t('Choose')}
            options={options.document_types || []}
            onChange={(v) => onChange({ ...step, config: { document_type: v } })}
          />
        </Col>
      );
      break;
    case 'esign':
      config = (
        <Col xs={24} sm={16}>
          <Text>{t('Document to sign')}</Text>
          <Select style={{ width: '100%' }} showSearch filterOption={filterOption} value={step.config.document || undefined} placeholder={t('Choose')} options={options.esign_documents || []} onChange={(v) => setConfig({ document: v })} />
        </Col>
      );
      break;
    case 'course':
      config = (
        <Col xs={24} sm={16}>
          <Text>{t('Course')}</Text>
          <Select style={{ width: '100%' }} showSearch filterOption={filterOption} value={step.config.course || undefined} placeholder={t('Choose')} options={options.courses || []} onChange={(v) => setConfig({ course: v })} />
        </Col>
      );
      break;
    case 'asset':
      config = (
        <Col xs={24} sm={12}>
          <Text>{t('Asset type')}</Text>
          <Select
            style={{ width: '100%' }}
            allowClear
            showSearch
            filterOption={filterOption}
            value={step.config.asset_type || undefined}
            placeholder={step.config.asset_type_name || t('Any')}
            options={options.asset_types || []}
            onChange={(v) => onChange({ ...step, config: v ? { asset_type: v } : {} })}
          />
        </Col>
      );
      break;
    default:
      break;
  }

  return (
    <Card
      size="small"
      style={{ marginBottom: 10 }}
      title={(
        <Space size={8} wrap>
          <Tag>{index + 1}</Tag>
          <Text strong>{step.title || t('Untitled step')}</Text>
          <Tag color="blue">{typeLabel(step.type)}</Tag>
          {unavailable ? <Tag color="red">{t('Extension not available')}</Tag> : null}
        </Space>
      )}
      extra={(
        <Space size={2}>
          <Button size="small" type="text" icon={<ArrowUpOutlined />} disabled={index === 0} onClick={() => onMove(-1)} />
          <Button size="small" type="text" icon={<ArrowDownOutlined />} disabled={index === count - 1} onClick={() => onMove(1)} />
          <Button size="small" type="text" danger icon={<DeleteOutlined />} onClick={onRemove} />
        </Space>
      )}
    >
      <Row gutter={[12, 10]}>
        <Col xs={24} sm={14}>
          <Text>{t('Title')}</Text>
          <Input value={step.title} onChange={(e) => set({ title: e.target.value })} maxLength={200} />
        </Col>
        <Col xs={24} sm={10}>
          <Text>{t('Type')}</Text>
          <Select
            style={{ width: '100%' }}
            value={step.type}
            /* Types whose extension is not installed are left out (kept only for a step that already uses one). */
            options={STEP_TYPES.filter((type) => type === step.type || !options.available || options.available[type] !== false).map((type) => ({ value: type, label: typeLabel(type), disabled: options.available && options.available[type] === false }))}
            onChange={(type) => {
              const fresh = newStep(type);
              onChange({
                ...step, type, owner_type: fresh.owner_type, needs_review: fresh.needs_review, config: fresh.config,
              });
            }}
          />
        </Col>
        <Col xs={24}><Text type="secondary" style={{ fontSize: 12 }}>{typeHelp(step.type)}</Text></Col>
        {config}
        <Col xs={24}>
          <Text>{t('Instructions (optional)')}</Text>
          <Input.TextArea value={step.instructions || ''} onChange={(e) => set({ instructions: e.target.value })} autoSize={{ minRows: 1, maxRows: 6 }} maxLength={5000} />
        </Col>
        <Col xs={24} sm={8}>
          <Text>{t('Done by')}</Text>
          {forHire || step.type === 'set_manager' ? (
            <Input value={forHire ? ownerLabel('new_hire', track) : ownerLabel('hr')} disabled />
          ) : (
            <Select
              style={{ width: '100%' }}
              value={step.owner_type}
              options={['hr', 'manager', 'buddy', 'employee'].map((o) => ({ value: o, label: ownerLabel(o) }))}
              onChange={(v) => set({ owner_type: v, owner_employee: v === 'employee' ? step.owner_employee : null })}
            />
          )}
        </Col>
        {!forHire && step.owner_type === 'employee' ? (
          <Col xs={24} sm={16}>
            <Text>{t('Person')}</Text>
            <Select style={{ width: '100%' }} showSearch filterOption={filterOption} value={step.owner_employee || undefined} options={people} onChange={(v) => set({ owner_employee: v })} placeholder={t('Choose who does this step')} />
          </Col>
        ) : null}
        <Col xs={12} sm={8}>
          <Text>{track === 'workforce' ? t('Due (days after the invitation)') : t('Due (days from start)')}</Text>
          <InputNumber
            style={{ width: '100%' }}
            min={-365}
            max={365}
            value={step.due_offset_days}
            onChange={(v) => set({ due_offset_days: v === null ? 0 : v, phase: phaseFor(v) })}
          />
          <Text type="secondary" style={{ fontSize: 12 }}>{dueLabel(step.due_offset_days, track)}</Text>
        </Col>
        <Col xs={12} sm={8}>
          <Text>{t('Shown under')}</Text>
          <Select style={{ width: '100%' }} value={step.phase} options={PHASES.map((p) => ({ value: p, label: phaseLabel(p, track) }))} onChange={(v) => set({ phase: v })} />
        </Col>
        <Col xs={24}>
          <Space size={16} wrap>
            <Checkbox checked={step.required !== false} onChange={(e) => set({ required: e.target.checked })}>{t('Required')}</Checkbox>
            {step.type === 'document' ? (
              <Checkbox checked={step.needs_review !== false} onChange={(e) => set({ needs_review: e.target.checked })}>{t('HR reviews the document')}</Checkbox>
            ) : null}
          </Space>
        </Col>
      </Row>
    </Card>
  );
}

/** Edit a custom workflow: welcome content and its steps. */
export default function WorkflowBuilder({
  api, ctx, id, open, onClose, onSaved,
}) {
  const [workflow, setWorkflow] = useState(null);
  const [people, setPeople] = useState([]);
  const [saving, setSaving] = useState(false);
  const [errors, setErrors] = useState(null);
  // The workflow as last loaded or saved, to tell whether there are unsaved changes.
  const [savedJson, setSavedJson] = useState(null);
  const [modal, modalContext] = Modal.useModal();
  const options = (ctx && ctx.options) || {};

  useEffect(() => {
    if (!open || !id) return;
    setWorkflow(null);
    setSavedJson(null);
    setErrors(null);
    api.get(`onboarding/workflows/${id}`)
      .then((w) => {
        const loaded = { ...w, steps: (w.steps || []).map((s) => ({ ...s, config: s.config || {} })) };
        setWorkflow(loaded);
        setSavedJson(JSON.stringify(loaded));
      })
      .catch((e) => message.error(errorMessage(e, t('The workflow could not be loaded'))));
    api.get('onboarding/lookups').then((l) => setPeople((l && l.people) || [])).catch(() => {});
  }, [open, id]);

  const set = (patch) => setWorkflow({ ...workflow, ...patch });
  const setStep = (index, step) => set({ steps: workflow.steps.map((s, i) => (i === index ? step : s)) });
  const move = (index, delta) => {
    const steps = workflow.steps.slice();
    const [item] = steps.splice(index, 1);
    steps.splice(index + delta, 0, item);
    set({ steps });
  };
  const add = (type) => set({ steps: workflow.steps.concat([newStep(type)]) });

  const save = () => {
    setSaving(true);
    setErrors(null);
    api.post(`onboarding/workflows/${id}`, workflow)
      .then(() => {
        message.success(t('Workflow saved'));
        setSavedJson(JSON.stringify(workflow));
        if (onSaved) onSaved();
      })
      .catch((e) => setErrors(errorMessage(e, t('The workflow could not be saved'))))
      .finally(() => setSaving(false));
  };

  const addMenu = {
    items: STEP_TYPES.filter((type) => !options.available || options.available[type] !== false)
      .map((type) => ({ key: type, label: typeLabel(type) })),
    onClick: ({ key }) => add(key),
  };

  const dirty = !!workflow && savedJson !== null && JSON.stringify(workflow) !== savedJson;
  // Closing (X, Esc or a click outside) asks first when there are unsaved changes.
  const requestClose = () => {
    if (!dirty) {
      onClose();
      return;
    }
    modal.confirm({
      title: t('Discard your changes?'),
      content: t('The changes to this workflow are not saved.'),
      okText: t('Discard changes'),
      okButtonProps: { danger: true },
      cancelText: t('Keep editing'),
      onOk: () => onClose(),
    });
  };

  return (
    <Drawer
      open={open}
      onClose={requestClose}
      width={Math.min(880, typeof window !== 'undefined' ? window.innerWidth : 880)}
      title={workflow ? workflow.name : t('Workflow')}
      extra={<Button type="primary" icon={<SaveOutlined />} loading={saving} disabled={!workflow} onClick={save}>{t('Save')}</Button>}
      destroyOnClose
    >
      {modalContext}
      {!workflow ? <Skeleton active /> : (
        <>
          {errors ? <Alert type="error" showIcon style={{ marginBottom: 16, whiteSpace: 'pre-line' }} message={errors} /> : null}
          <Row gutter={[12, 10]} style={{ marginBottom: 16 }}>
            <Col xs={24} sm={12}>
              <Text>{t('Name')}</Text>
              <Input value={workflow.name} onChange={(e) => set({ name: e.target.value })} maxLength={150} />
            </Col>
            <Col xs={24} sm={12}>
              <Text>{t('Welcome video link (optional)')}</Text>
              <Input value={workflow.welcome_video_url || ''} onChange={(e) => set({ welcome_video_url: e.target.value })} placeholder="https://" maxLength={500} />
            </Col>
            <Col xs={24}>
              <Text>{t('Description')}</Text>
              <Input.TextArea value={workflow.description || ''} onChange={(e) => set({ description: e.target.value })} autoSize={{ minRows: 1, maxRows: 4 }} maxLength={2000} />
            </Col>
            <Col xs={24} sm={12}>
              <Text>{t('Welcome message')}</Text>
              <Input.TextArea value={workflow.welcome_message || ''} onChange={(e) => set({ welcome_message: e.target.value })} autoSize={{ minRows: 3, maxRows: 8 }} maxLength={5000} />
            </Col>
            <Col xs={24} sm={12}>
              <Text>{t('First day information')}</Text>
              <Input.TextArea value={workflow.first_day_info || ''} onChange={(e) => set({ first_day_info: e.target.value })} autoSize={{ minRows: 3, maxRows: 8 }} maxLength={5000} />
            </Col>
          </Row>
          <div style={{
            display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10,
          }}
          >
            <Text strong>{t('Steps')}</Text>
            <Dropdown menu={addMenu}><Button icon={<PlusOutlined />}>{t('Add a step')}</Button></Dropdown>
          </div>
          {workflow.steps.length === 0 ? <Empty description={t('No steps yet')} /> : null}
          {workflow.steps.map((step, i) => (
            <StepEditor
              key={step.key}
              step={step}
              index={i}
              count={workflow.steps.length}
              options={options}
              people={people}
              track={workflow.track}
              onChange={(s) => setStep(i, s)}
              onMove={(d) => move(i, d)}
              onRemove={() => set({ steps: workflow.steps.filter((_, j) => j !== i) })}
            />
          ))}
          {workflow.steps.length > 0 ? (
            <Dropdown menu={addMenu}><Button block icon={<PlusOutlined />}>{t('Add a step')}</Button></Dropdown>
          ) : null}
        </>
      )}
    </Drawer>
  );
}
