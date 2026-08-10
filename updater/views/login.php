<?php
/**
 * Sign-in. Always shown on arrival: the updater cannot validate an IceHRM session
 * (it must keep working while core/ is being replaced), so it never tries.
 */
?>
<?php if (!empty($error)): ?>
  <div class="msg error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!empty($recovery)): ?>
  <?php // Reached without an update link because IceHRM is not answering. Say so
        // plainly: the administrator needs to know the application is down, and that
        // this is the recovery route rather than the normal one. ?>
  <div class="msg warn">
    <strong>IceHRM is not responding</strong>
    <?= !empty($recovery['status']) ? ' (HTTP ' . (int) $recovery['status'] . ')' : '' ?>,
    so the updater opened without an update link. Sign in below to repair or update this
    installation.
  </div>
<?php endif; ?>
<p>Sign in with an IceHRM <strong>administrator</strong> account to update this installation.</p>
<form method="post">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(UpdaterAuth::csrfToken()) ?>">
  <input type="hidden" name="action" value="login">
  <label for="username">Username or email</label>
  <input type="text" id="username" name="username" autocomplete="username" autofocus required>
  <label for="password">Password</label>
  <input type="password" id="password" name="password" autocomplete="current-password" required>
  <button type="submit">Sign in</button>
</form>
