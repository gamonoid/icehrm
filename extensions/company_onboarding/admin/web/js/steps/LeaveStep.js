import React from 'react';
import { Typography } from 'antd';
import { t } from '../../../../../../web/api/i18n';
import ListEditor from '../components/ListEditor';
import StepFooter from '../components/StepFooter';

const { Text } = Typography;

/** Step 6 (optional): leave types and how many days a year each gives. */
export default function LeaveStep({
  api, step, busy, onContinue, onSkip,
}) {
  return (
    <>
      <Text type="secondary" style={{ display: 'block', marginBottom: 12 }}>
        {t('Set how many days a year each type gives. Carry-over, accrual and approval rules can be set later in Leave settings.')}
      </Text>
      <ListEditor
        api={api}
        listKey="leave_types"
        withDays
        placeholder={t('Type a leave type, e.g. Annual leave')}
        suggestions={[
          { name: t('Annual leave'), days: 14 },
          { name: t('Sick leave'), days: 7 },
          { name: t('Casual leave'), days: 7 },
          { name: t('Maternity leave'), days: 84 },
          { name: t('Paternity leave'), days: 5 },
          { name: t('Unpaid leave'), days: 0 },
        ]}
      />
      <StepFooter step={step} busy={busy} onSkip={onSkip} onContinue={onContinue} />
    </>
  );
}
