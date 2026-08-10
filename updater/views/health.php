<?php
/**
 * Result of the post-update check.
 *
 * The three outcomes get deliberately different emphasis. Offering a prominent
 * "restore the previous version" button whenever the HTTP probe fails would push
 * administrators into rolling back perfectly good updates, because a server being
 * unable to reach its own public URL is ordinary — see UpdaterHealth. So rollback is
 * the primary action only when the application actually answered with an error.
 */
?>
<?php if ($health['state'] === 'healthy'): ?>
  <div class="msg ok"><?= htmlspecialchars($health['message']) ?></div>
  <p>The update is complete. The backup and the downloaded files have been removed.</p>
  <p class="detail">
    Files verified: <?= htmlspecialchars($health['fileDetail']) ?>.
    <?php if (!empty($health['via'])): ?>Checked via <code><?= htmlspecialchars($health['via']) ?></code>.<?php endif; ?>
    The update log is kept at <code>updater/data/update.log</code>.
  </p>

<?php elseif ($health['state'] === 'broken'): ?>
  <div class="msg error"><?= htmlspecialchars($health['message']) ?></div>
  <p>The application responded with an error after the update. You can restore the previous
     version now, or update manually.</p>
  <p class="detail">Files on disk: <?= htmlspecialchars($health['fileDetail']) ?>.</p>
  <?php if (!empty($backup)): ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(UpdaterAuth::csrfToken()) ?>">
      <input type="hidden" name="action" value="rollback">
      <button class="danger" type="submit">Restore the previous version</button>
    </form>
  <?php endif; ?>
  <p style="margin-top:16px"><a href="https://icehrm.com/docs/installation/upgrade" target="_blank" rel="noopener">Manual upgrade instructions</a></p>

<?php elseif ($health['filesOk']): ?>
  <?php // Files verified on disk; only the network probe failed. Reassure, do not alarm. ?>
  <div class="msg ok">The new version is installed — <?= htmlspecialchars($health['fileDetail']) ?>.</div>
  <div class="msg warn"><?= $health['message'] ?></div>
  <p><strong>Open IceHRM in your browser to confirm</strong>
     <?php if (defined('CLIENT_BASE_URL')): ?>
       — <a href="<?= htmlspecialchars(CLIENT_BASE_URL) ?>" target="_blank" rel="noopener"><?= htmlspecialchars(CLIENT_BASE_URL) ?></a>
     <?php endif; ?>.
     If it works, the update is finished and nothing further is needed.</p>
  <p class="detail">
    The previous version is still in <code><?= htmlspecialchars((string) $backup) ?></code>. Delete it
    once you are satisfied, or use the option below if IceHRM does not load.
  </p>
  <?php if (!empty($backup)): ?>
    <details style="margin-top:14px">
      <summary class="detail" style="cursor:pointer">IceHRM does not load — restore the previous version</summary>
      <form method="post" style="margin-top:10px">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(UpdaterAuth::csrfToken()) ?>">
        <input type="hidden" name="action" value="rollback">
        <button class="danger" type="submit">Restore the previous version</button>
      </form>
      <p class="detail" style="margin-top:8px">
        Only do this if the application genuinely fails to load in a browser. The check above
        failing on its own does not mean the update failed.
      </p>
    </details>
  <?php endif; ?>

<?php else: ?>
  <?php // Could not reach it AND could not verify the files — the one case to worry about. ?>
  <div class="msg error">The update could not be verified: <?= htmlspecialchars($health['fileDetail']) ?>.</div>
  <div class="msg warn"><?= $health['message'] ?></div>
  <p>Open IceHRM in your browser. If it does not load, restore the previous version.</p>
  <?php if (!empty($backup)): ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(UpdaterAuth::csrfToken()) ?>">
      <input type="hidden" name="action" value="rollback">
      <button class="danger" type="submit">Restore the previous version</button>
    </form>
  <?php endif; ?>
  <p style="margin-top:16px"><a href="https://icehrm.com/docs/installation/upgrade" target="_blank" rel="noopener">Manual upgrade instructions</a></p>
<?php endif; ?>

<h2>Log</h2>
<pre><?= htmlspecialchars(UpdaterLog::tail(60)) ?></pre>
