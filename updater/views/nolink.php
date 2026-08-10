<?php
/**
 * Shown when the updater is opened without a valid signed link.
 *
 * Deliberately gives nothing away: no sign-in form, no version details, no indication
 * of whether this is even a working installation. The updater can replace every file on
 * the server, so an unauthenticated visitor should learn as little from it as possible.
 */
?>
<div class="msg warn"><?= htmlspecialchars($explanation) ?></div>

<p>To update IceHRM:</p>
<ol>
  <li>Sign in to IceHRM as an administrator.</li>
  <li>On the dashboard, use <strong>Update</strong> on the “new version available” banner.</li>
</ol>

<?php if (defined('CLIENT_BASE_URL')): ?>
  <p><a href="<?= htmlspecialchars(CLIENT_BASE_URL) ?>">Open IceHRM</a></p>
<?php endif; ?>

<p class="detail" style="margin-top:18px">
  If IceHRM itself will not load, generate a link on the server instead:
</p>
<pre>php <?= htmlspecialchars(UpdaterBootstrap::$updaterDir) ?>/token.php</pre>
