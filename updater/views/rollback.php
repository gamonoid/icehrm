<?php /** Outcome of restoring the backup. */ ?>
<?php if ($rollback['ok']): ?>
  <div class="msg ok"><?= htmlspecialchars($rollback['message']) ?></div>
  <p>Your previous version is back in place. Open IceHRM to confirm, then investigate the log
     below before trying the update again.</p>
<?php else: ?>
  <div class="msg error"><?= htmlspecialchars($rollback['message']) ?></div>
  <p>Restore the directories from <code><?= htmlspecialchars((string) $backup) ?></code> manually, or follow the
     <a href="https://icehrm.com/docs/installation/upgrade" target="_blank" rel="noopener">manual upgrade instructions</a>.</p>
<?php endif; ?>

<h2>Log</h2>
<pre><?= htmlspecialchars(UpdaterLog::tail(80)) ?></pre>
