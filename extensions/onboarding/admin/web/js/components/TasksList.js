import React, { useEffect, useState } from 'react';
import {
  Button, Card, Empty, Input, Modal, Select, Skeleton, Space, Switch, Typography, message,
} from 'antd';
import { CheckOutlined } from '@ant-design/icons';
import { t } from '../../../../../../web/api/i18n';
import { errorMessage } from '../shared/api';
import { formatDate } from '../shared/constants';
import StepList from '../shared/StepList';
import SetManagerModal from './SetManagerModal';

const { Text, Paragraph } = Typography;

/**
 * Onboarding tasks assigned to the signed-in person (as HR, IT, manager, buddy or a
 * named person), grouped by new hire. Used in the admin view and in My Onboarding.
 */
export default function TasksList({ api, refreshKey, onChanged }) {
  const [rows, setRows] = useState(null);
  const [showDone, setShowDone] = useState(false);
  const [doneFor, setDoneFor] = useState(null);
  const [note, setNote] = useState('');
  const [assets, setAssets] = useState([]);
  const [asset, setAsset] = useState(null);
  const [busy, setBusy] = useState(false);
  const [managerFor, setManagerFor] = useState(null);

  const load = () => {
    setRows(null);
    api.get('onboarding/me/tasks', { done: showDone ? 1 : '' })
      .then(setRows)
      .catch(() => setRows([]));
  };
  useEffect(load, [showDone, refreshKey]);

  const open = (step) => {
    if (step.type === 'set_manager') {
      setManagerFor(step);
      return;
    }
    setNote('');
    setAsset(null);
    setDoneFor(step);
    if (step.type === 'asset') {
      api.get('onboarding/assets', { type: step.asset_type || '' }).then(setAssets).catch(() => setAssets([]));
    }
  };

  const submit = () => {
    setBusy(true);
    api.post(`onboarding/me/tasks/${doneFor.id}/done`, { note, asset })
      .then(() => {
        message.success(t('Task done'));
        setDoneFor(null);
        load();
        if (onChanged) onChanged();
      })
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))))
      .finally(() => setBusy(false));
  };

  const groups = [];
  (rows || []).forEach((step) => {
    let g = groups.find((x) => x.id === step.onboarding);
    if (!g) {
      g = {
        id: step.onboarding, name: step.hire_name, start: step.start_date, track: step.track, steps: [],
      };
      groups.push(g);
    }
    g.steps.push(step);
  });

  return (
    <div>
      <Space style={{ marginBottom: 16 }}>
        <Switch checked={showDone} onChange={setShowDone} size="small" />
        <Text>{t('Show done tasks')}</Text>
      </Space>
      {rows === null ? <Skeleton active /> : null}
      {rows && rows.length === 0 ? <Empty description={t('You have no onboarding tasks.')} /> : null}
      {groups.map((g) => (
        <Card
          key={g.id}
          size="small"
          style={{ marginBottom: 16 }}
          title={g.name}
          extra={g.track === 'workforce' ? (
            <Text type="secondary">{t('Existing employee')}</Text>
          ) : <Text type="secondary">{t('Starts {date}', { date: formatDate(g.start) })}</Text>}
        >
          <StepList
            steps={g.steps}
            track={g.track}
            renderActions={(step) => (step.can_complete ? (
              <Button size="small" type="primary" ghost icon={<CheckOutlined />} onClick={() => open(step)}>
                {step.type === 'asset' ? t('Hand over') : null}
                {step.type === 'set_manager' ? t('Set manager') : null}
                {step.type !== 'asset' && step.type !== 'set_manager' ? t('Mark as done') : null}
              </Button>
            ) : null)}
          />
        </Card>
      ))}

      <SetManagerModal
        api={api}
        step={managerFor}
        donePath={managerFor ? `onboarding/me/tasks/${managerFor.id}/done` : ''}
        onClose={() => setManagerFor(null)}
        onDone={() => { setManagerFor(null); load(); if (onChanged) onChanged(); }}
      />
      <Modal
        open={!!doneFor}
        title={doneFor && doneFor.type === 'asset' ? t('Hand over an asset') : t('Mark as done')}
        okText={doneFor && doneFor.type === 'asset' ? t('Hand over') : t('Mark as done')}
        okButtonProps={{ disabled: doneFor && doneFor.type === 'asset' && !asset }}
        confirmLoading={busy}
        onCancel={() => setDoneFor(null)}
        onOk={submit}
      >
        {doneFor && doneFor.type === 'asset' ? (
          <>
            <Paragraph type="secondary">{t('Choose an available asset. It is assigned to the new hire in Company Assets.')}</Paragraph>
            <Select
              style={{ width: '100%', marginBottom: 12 }}
              showSearch
              value={asset}
              onChange={setAsset}
              options={assets}
              placeholder={t('Choose an asset')}
              notFoundContent={t('No available assets of this type')}
              filterOption={(input, option) => String(option.label).toLowerCase().includes(input.toLowerCase())}
            />
          </>
        ) : null}
        <Text type="secondary">{t('Add a note if it helps (optional).')}</Text>
        <Input.TextArea style={{ marginTop: 8 }} value={note} onChange={(e) => setNote(e.target.value)} autoSize={{ minRows: 2, maxRows: 6 }} maxLength={2000} />
      </Modal>
    </div>
  );
}
