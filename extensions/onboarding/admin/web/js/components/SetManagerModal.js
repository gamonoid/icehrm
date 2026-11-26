import React, { useEffect, useState } from 'react';
import {
  Modal, Select, Tag, Typography, message,
} from 'antd';
import { t } from '../../../../../../web/api/i18n';
import { errorMessage } from '../shared/api';

const { Text, Paragraph } = Typography;

const filterOption = (input, option) => String(option.search || '').toLowerCase().includes(input.toLowerCase());

/**
 * Complete a "Set manager" step: choose who the person reports to. Active employees
 * and people whose onboarding is in progress can be chosen; people only invited are
 * listed but disabled until they accept. `donePath` is the endpoint that completes
 * the step (HR's drawer or the tasks list).
 */
export default function SetManagerModal({
  api, step, donePath, onClose, onDone,
}) {
  const [options, setOptions] = useState(null);
  const [manager, setManager] = useState(null);
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (!step) return;
    setManager(null);
    setOptions(null);
    api.get(`onboarding/steps/${step.id}/manager-options`)
      .then(setOptions)
      .catch((e) => {
        message.error(errorMessage(e, t('The list of people could not be loaded')));
        setOptions([]);
      });
  }, [step && step.id]);

  const submit = () => {
    setBusy(true);
    api.post(donePath, { manager })
      .then(() => {
        message.success(t('Manager set'));
        if (onDone) onDone();
      })
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))))
      .finally(() => setBusy(false));
  };

  return (
    <Modal
      open={!!step}
      title={t('Set manager')}
      okText={t('Set manager')}
      okButtonProps={{ disabled: !manager }}
      confirmLoading={busy}
      onOk={submit}
      onCancel={onClose}
      destroyOnClose
    >
      <Paragraph type="secondary">
        {t('Choose who this person reports to. People still being onboarded can be chosen once they have accepted their invitation.')}
      </Paragraph>
      <Select
        style={{ width: '100%' }}
        showSearch
        loading={options === null}
        value={manager || undefined}
        onChange={setManager}
        filterOption={filterOption}
        placeholder={t('Choose the manager')}
        options={(options || []).map((o) => ({
          value: o.value,
          disabled: o.disabled,
          search: o.label,
          label: (
            <span style={{ display: 'flex', justifyContent: 'space-between', gap: 8 }}>
              <span>{o.label}</span>
              {o.disabled ? <Text type="secondary" style={{ fontSize: 12 }}>{t('Hasn\'t accepted yet')}</Text> : null}
              {!o.disabled && o.onboarding ? <Tag color="blue" style={{ margin: 0 }}>{t('Onboarding')}</Tag> : null}
            </span>
          ),
        }))}
      />
    </Modal>
  );
}
