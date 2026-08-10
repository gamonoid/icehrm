const { test, expect } = require('@playwright/test');
const { login, openModule } = require('../helpers/app');

// Viewing a leave attachment in the new UI must render the download link + image
// preview, not the raw HTML as text. The download() helper (web/api-common) builds
// an HTML string and hands it to modJs.showMessage; in the SPA shell showMessage
// now renders that as HTML (matching legacy's jQuery .html()), instead of letting
// React escape it. Seed leave id 1840 (admin, Medical leave) has a .png attachment.
test.describe('modules::leaves — view attachment', () => {
  test('renders the attachment link and image, not raw HTML text', async ({ page }) => {
    await page.setViewportSize({ width: 1400, height: 1000 });
    await login(page, 'admin');
    await openModule(page, 'modules::leaves');
    await page.waitForTimeout(2000);

    // Open the leave details dialog for the seeded leave with an attachment.
    await page.evaluate(() => window.modJs.getLeaveDaysReadonly(1840));
    const viewBtn = page.getByRole('button', { name: /View Attachment/i });
    await expect(viewBtn).toBeVisible({ timeout: 15000 });
    await viewBtn.click();

    // The "Download File Attachment" dialog renders a real anchor + <img>, both
    // pointing at the signed download URL — and shows no escaped markup text.
    const dialog = page.locator('.ant-modal', { hasText: 'Download File Attachment' });
    await expect(dialog).toBeVisible({ timeout: 15000 });
    await expect(dialog.locator('a', { hasText: /Download File/i })).toBeVisible();
    const img = dialog.locator('img');
    await expect(img).toBeVisible();
    await expect(img).toHaveAttribute('src', /a=download/);
    await expect(dialog.getByText('<img', { exact: false })).toHaveCount(0);
  });
});
