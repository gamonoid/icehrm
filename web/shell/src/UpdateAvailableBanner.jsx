import React from 'react';
import { Alert, Button, Space } from 'antd';

const DISMISS_KEY = 'icehrm-update-banner-dismissed';

/**
 * "A newer IceHRM is available" banner.
 *
 * The payload is built server-side by Classes\UpdateAvailability and is null unless the
 * signed-in user is an administrator AND the marketplace snapshot advertises a version
 * newer than the installed one — so there is no version comparison, and no notion of
 * who may see this, in the browser.
 *
 * Dismissal is per browser session and keyed on the version, so it comes back when a
 * newer release appears and after the next sign-in, but does not nag on every dashboard
 * visit in between.
 *
 * The Update link is single-use in spirit: it carries a signature that expires two hours
 * after the page was rendered. Opening it in a new tab keeps the admin's IceHRM session
 * intact, which matters because the updater deliberately runs its own separate session.
 */
export default function UpdateAvailableBanner({ updateAvailable }) {
  const version = updateAvailable ? updateAvailable.latestVersion : null;

  const [dismissed, setDismissed] = React.useState(() => {
    if (!version) return false;
    try {
      return window.sessionStorage.getItem(DISMISS_KEY) === version;
    } catch (e) {
      return false;
    }
  });

  if (!updateAvailable || dismissed) return null;

  const { currentVersion, latestVersion, changelogUrl, updaterUrl } = updateAvailable;

  const dismiss = () => {
    try {
      window.sessionStorage.setItem(DISMISS_KEY, latestVersion);
    } catch (e) {
      // Private browsing or a full quota: dismissing for this render is enough.
    }
    setDismissed(true);
  };

  return (
    <Alert
      type="info"
      banner
      showIcon
      closable
      onClose={dismiss}
      style={{ padding: '18px 28px', alignItems: 'center', marginBottom: 16 }}
      message={(
        <div style={{ lineHeight: 1.35 }}>
          <div style={{ fontSize: 17, fontWeight: 700, marginBottom: 3 }}>
            IceHRM {latestVersion} is available
          </div>
          <div style={{ fontSize: 14, opacity: 0.9 }}>
            You are running {currentVersion}. Updating replaces the program files; your
            settings, uploads and data are kept, and the previous version is backed up
            so the update can be undone.
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
          {updaterUrl ? (
            <Button
              size="large"
              type="primary"
              href={updaterUrl}
              target="_blank"
              rel="noopener noreferrer"
              style={{ fontWeight: 600 }}
            >
              Update
            </Button>
          ) : null}
        </Space>
      )}
    />
  );
}
