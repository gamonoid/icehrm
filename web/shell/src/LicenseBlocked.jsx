import React from 'react';
import { Result, Button } from 'antd';

export default function LicenseBlocked({ license }) {
  const lic = license || {};
  const missing = !lic.has_license;
  const expiredOn = lic.expiry_date ? String(lic.expiry_date).split(' ')[0] : null;
  const title = missing
    ? 'No active IceHrmPro license'
    : 'Your IceHrmPro license has expired';
  const subTitle = missing
    ? 'This feature is part of IceHrmPro and needs an active subscription to use.'
    : `Your IceHrmPro license${expiredOn ? ` expired on ${expiredOn}` : ' has expired'}. Renew it to keep using this feature.`;

  const goToMarketplace = () => {
    if (typeof window !== 'undefined') {
      window.location.hash = `#${encodeURIComponent('extension::marketplace|admin')}`;
    }
  };

  return (
    <div style={{ padding: 24 }}>
      <Result
        status="warning"
        title={title}
        subTitle={subTitle}
        extra={lic.is_admin ? (
          <Button type="primary" onClick={goToMarketplace}>
            Go to Marketplace
          </Button>
        ) : null}
      />
    </div>
  );
}
