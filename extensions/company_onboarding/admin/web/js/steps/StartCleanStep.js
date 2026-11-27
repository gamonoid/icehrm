import React, { useState } from 'react';
import {
  Alert, Button, Popconfirm, Typography, message,
} from 'antd';
import { DeleteOutlined } from '@ant-design/icons';
import { t } from '../../../../../../web/api/i18n';
import { errorMessage } from '../api';
import StepFooter from '../components/StepFooter';

const { Paragraph } = Typography;

/** Step 0: remove the sample data (only offered while sample data is loaded). */
export default function StartCleanStep({
  api, step, summary, busy, onContinue, onSkip, onRefresh,
}) {
  const [removing, setRemoving] = useState(false);
  const hasDemo = summary.demo_data > 0;

  const remove = () => {
    setRemoving(true);
    api.del('demo-mode/data')
      .then(() => {
        message.success(t('Sample data removed'));
        onContinue();
      })
      .catch((e) => {
        message.error(errorMessage(e, t('The sample data could not be removed')));
        onRefresh();
      })
      .finally(() => setRemoving(false));
  };

  return (
    <>
      {hasDemo ? (
        <>
          <Alert
            type="warning"
            showIcon
            message={t('Sample data is loaded')}
            description={t('Sample employees, attendance, leave and other example records were added so you could try IceHrm. Remove them before you add your own company.')}
          />
          <Paragraph style={{ marginTop: 16 }}>
            <Popconfirm
              title={t('Remove all sample data?')}
              description={t('Every sample record is deleted. Anything you added yourself is kept.')}
              okText={t('Remove')}
              okButtonProps={{ danger: true }}
              cancelText={t('Cancel')}
              onConfirm={remove}
            >
              <Button danger icon={<DeleteOutlined />} loading={removing}>{t('Remove sample data')}</Button>
            </Popconfirm>
          </Paragraph>
        </>
      ) : (
        <Alert type="success" showIcon message={t('There is no sample data.')} />
      )}
      <StepFooter
        step={step}
        busy={busy || removing}
        onSkip={onSkip}
        onContinue={onContinue}
        continueDisabled={hasDemo}
      />
    </>
  );
}
