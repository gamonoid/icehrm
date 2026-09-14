import React from 'react';
import { Alert, Button } from 'antd';

export default function LicenseRenewalBanner({ licenseRenewal }) {
  if (!licenseRenewal) return null;

  const {
    daysLeft, expiryDate, isAdmin, renewUrl,
  } = licenseRenewal;
  const dateStr = expiryDate ? String(expiryDate).split(' ')[0] : null;
  const whenLabel = daysLeft <= 0
    ? 'today'
    : (daysLeft === 1 ? 'in 1 day' : `in ${daysLeft} days`);

  return (
    <Alert
      type="warning"
      banner
      showIcon
      style={{ padding: '18px 28px', alignItems: 'center', marginBottom: 16 }}
      message={(
        <div style={{ lineHeight: 1.35 }}>
          <div style={{ fontSize: 17, fontWeight: 700, marginBottom: 3 }}>
            Your IceHrmPro license expires {whenLabel}
          </div>
          <div style={{ fontSize: 14, opacity: 0.9 }}>
            {dateStr ? `It expires on ${dateStr}. ` : ''}
            Renew now to keep access to your premium features without interruption.
          </div>
        </div>
      )}
      action={(isAdmin && renewUrl) ? (
        <Button
          size="large"
          type="primary"
          href={renewUrl}
          target="_blank"
          rel="noopener noreferrer"
          style={{ fontWeight: 600 }}
        >
          Renew IceHrm Pro
        </Button>
      ) : null}
    />
  );
}
