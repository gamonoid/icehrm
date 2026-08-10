<?php
/**
 * Files are in place (or failed). The database is NOT yet migrated: that happens when
 * an administrator signs in, which is why this screen asks for exactly that before
 * checking the application.
 */
?>
<?php if ($result['ok']): ?>
  <div class="msg ok"><?= htmlspecialchars($result['message']) ?></div>

  <?php if (!empty($result['preserved'])): ?>
    <div class="msg warn">
      These extensions were not part of the release and have been left untouched:
      <code><?= htmlspecialchars(implode('</code>, <code>', $result['preserved'])) ?></code>.
      Check they still work with the new version.
    </div>
  <?php endif; ?>

  <h2>Finish the update</h2>
  <ol>
    <li><strong>Sign in to IceHRM as an administrator</strong> in another tab —
        <a href="<?= htmlspecialchars(defined('CLIENT_BASE_URL') ? CLIENT_BASE_URL : '../app/') ?>" target="_blank" rel="noopener">open IceHRM</a>.
        Signing in applies any pending database changes; the files alone are not a complete update.</li>
    <li>Come back here and run the check below.</li>
  </ol>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(UpdaterAuth::csrfToken()) ?>">
    <input type="hidden" name="action" value="health">
    <button type="submit">I have signed in — check IceHRM</button>
  </form>

  <p class="detail" style="margin-top:14px">
    Your previous files are still in <code><?= htmlspecialchars((string) $result['backupDir']) ?></code>.
    They are removed once the check passes.
  </p>
<?php else: ?>
  <div class="msg error"><?= htmlspecialchars($result['message']) ?></div>
  <p>No further files have been changed. Your previous version can be restored.</p>
  <?php if (!empty($result['backupDir'])): ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(UpdaterAuth::csrfToken()) ?>">
      <input type="hidden" name="action" value="rollback">
      <button class="danger" type="submit">Restore the previous version</button>
    </form>
  <?php endif; ?>
<?php endif; ?>

<h2>Log</h2>
<pre><?= htmlspecialchars(UpdaterLog::tail(60)) ?></pre>
