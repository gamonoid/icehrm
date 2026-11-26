import React from 'react';
import { Button, Space } from 'antd';
import { t } from '../../../../../../web/api/i18n';

/**
 * Footer shared by every step: Continue (marks the step done) and, for optional
 * steps, Skip for now.
 */
export default function StepFooter({
  step, onContinue, onSkip, busy, continueLabel, continueDisabled, extra,
}) {
  return (
    <div style={{
      display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 8, flexWrap: 'wrap', marginTop: 24,
    }}
    >
      <div>{extra}</div>
      <Space wrap>
        {!step.required ? (
          <Button onClick={onSkip} disabled={busy}>{t('Skip for now')}</Button>
        ) : null}
        <Button type="primary" loading={busy} disabled={continueDisabled} onClick={onContinue}>
          {continueLabel || t('Continue')}
        </Button>
      </Space>
    </div>
  );
}
