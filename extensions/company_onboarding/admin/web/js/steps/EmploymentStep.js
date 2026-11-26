import React from 'react';
import { Typography } from 'antd';
import { t } from '../../../../../../web/api/i18n';
import ListEditor from '../components/ListEditor';
import StepFooter from '../components/StepFooter';

const { Title } = Typography;

/** Step 4 (optional): employment types. (Work locations are not part of IceHrm open source.) */
export default function EmploymentStep({
  api, step, busy, onContinue, onSkip,
}) {
  return (
    <>
      <Title level={5} style={{ marginTop: 0 }}>{t('Employment types')}</Title>
      <ListEditor
        api={api}
        listKey="employment"
        suggestions={[t('Full-time'), t('Part-time'), t('Contract'), t('Temporary'), t('Intern'), t('Probation')]}
      />
      <StepFooter step={step} busy={busy} onSkip={onSkip} onContinue={onContinue} />
    </>
  );
}
