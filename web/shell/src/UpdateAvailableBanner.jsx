import React from 'react';
import { Alert, Button, Space } from 'antd';

const DISMISS_KEY = 'icehrm-update-banner-dismissed';

/**
 * "A newer IceHRM is available" / "Move up to IceHRM Pro" banner.
 *
 * The payload is built server-side by Classes\UpdateAvailability and is null unless the
 * signed-in user is an administrator AND there is something to offer — so there is no
 * version comparison, no licence check, and no notion of who may see this, here.
 *
 * Two shapes arrive, told apart by `kind`:
 *
 *   'update'      a newer release of the edition already installed (Pro or open source)
 *   'pro-upgrade' an open-source installation whose owner is licensed for Pro
 *
 * Dismissal is per browser session and keyed on kind + version, so it comes back when a
 * newer release appears and after the next sign-in, but does not nag on every dashboard
 * visit in between.
 *
 * The Update link is single-use in spirit: it carries a signature that expires two hours
 * after the page was rendered. Opening it in a new tab keeps the admin's IceHRM session
 * intact, which matters because the updater deliberately runs its own separate session.
 */
export default function UpdateAvailableBanner({ updateAvailable }) {
  const isProUpgrade = !!updateAvailable && updateAvailable.kind === 'pro-upgrade';
  const dismissToken = updateAvailable
    ? `${updateAvailable.kind || 'update'}:${updateAvailable.latestVersion}`
    : null;

  const [dismissed, setDismissed] = React.useState(() => {
    if (!dismissToken) return false;
    try {
      return window.sessionStorage.getItem(DISMISS_KEY) === dismissToken;
    } catch (e) {
      return false;
    }
  });

  if (!updateAvailable || dismissed) return null;

  const {
    currentVersion, latestVersion, changelogUrl, updaterUrl, downloadsUrl,
  } = updateAvailable;

  const dismiss = () => {
    try {
      window.sessionStorage.setItem(DISMISS_KEY, dismissToken);
    } catch (e) {
      // Private browsing or a full quota: dismissing for this render is enough.
    }
    setDismissed(true);
  };

  const heading = isProUpgrade
    ? 'IceHRM Pro is included in your subscription'
    : `IceHRM ${latestVersion} is available`;

  const detail = isProUpgrade
    ? `You are running the open source edition ${currentVersion}. Your subscription covers `
      + `IceHRM Pro ${latestVersion} — Leave Management, Recruitment, Expenses, Performance, `
      + 'Payroll, Insights and more. Upgrading keeps your settings, uploads and data, and the '
      + 'previous version is backed up so it can be undone.'
    : `You are running ${currentVersion}. Updating replaces the program files; your `
      + 'settings, uploads and data are kept, and the previous version is backed up '
      + 'so the update can be undone.';

  return (
    <Alert
      type={isProUpgrade ? 'success' : 'info'}
      banner
      showIcon
      closable
      onClose={dismiss}
      style={{ padding: '18px 28px', alignItems: 'center', marginBottom: 16 }}
      message={(
        <div style={{ lineHeight: 1.35 }}>
          <div style={{ fontSize: 17, fontWeight: 700, marginBottom: 3 }}>
            {heading}
          </div>
          <div style={{ fontSize: 14, opacity: 0.9 }}>
            {detail}
          </div>
        </div>
      )}
      action={(
        <Space>
          {changelogUrl ? (
            <Button
              size="large"
              href={changelogUrl}
              target="_blank"
              rel="noopener noreferrer"
            >
              What&apos;s New
            </Button>
          ) : null}
          {/* Pro releases are downloaded with a signed, time-limited link the customer
              collects from their IceHRM account, so an open-source installation moving
              up to Pro is pointed at it before the updater asks for one. */}
          {isProUpgrade && downloadsUrl ? (
            <Button
              size="large"
              href={downloadsUrl}
              target="_blank"
              rel="noopener noreferrer"
            >
              Get Download Link
            </Button>
          ) : null}
          {updaterUrl ? (
            <Button
              size="large"
              type="primary"
              href={updaterUrl}
              target="_blank"
              rel="noopener noreferrer"
              style={{ fontWeight: 600 }}
            >
              {isProUpgrade ? 'Upgrade to Pro' : 'Update'}
            </Button>
          ) : null}
        </Space>
      )}
    />
  );
}
