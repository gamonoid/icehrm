<?php
/**
 * The last screen before anything is written. Everything up to here has been
 * non-destructive.
 */
?>
<?php $editionChange = !empty($editionChange); ?>
<div class="msg warn">
  This will replace the IceHRM program files on this server.
</div>
<?php if ($editionChange): ?>
  <p>
    Upgrade from <strong>IceHRM <?= htmlspecialchars(UpdaterPackage::formatVersion($currentVersion)) ?></strong>
    (open source) to <strong>IceHRM Pro <?= htmlspecialchars(UpdaterPackage::formatVersion($newVersion)) ?></strong>?
  </p>
<?php else: ?>
  <p>
    Update from <strong><?= htmlspecialchars(UpdaterPackage::formatVersion($currentVersion)) ?></strong>
    to <strong><?= htmlspecialchars(UpdaterPackage::formatVersion($newVersion)) ?></strong><?= UpdaterBootstrap::isPro() ? ' (Pro)' : '' ?>?
  </p>
<?php endif; ?>

<h2>What happens</h2>
<ul class="checks">
  <li><span class="mark ok">&#10003;</span><span>Your current <code>core/</code>, <code>web/</code>,
      <code>app/</code>, <code>bin/</code> and extension directories are moved into a backup inside
      <code>updater/data/</code> — not deleted — so the update can be undone.</span></li>
  <li><span class="mark ok">&#10003;</span><span>Your settings and uploads are kept:
      <code>app/config.php</code>, <code>app/data/</code> and <code>app/cache/</code> are never replaced.</span></li>
  <li><span class="mark ok">&#10003;</span><span>Extensions that are not part of the release —
      anything you installed yourself — are left in place.</span></li>
  <li><span class="mark bad">!</span><span>The application will be briefly unavailable while files
      are replaced. Ask users to sign out first if you can.</span></li>
<?php if ($editionChange): ?>
  <li><span class="mark ok">&#10003;</span><span>The paid modules are installed into
      <code>extensions-pro/</code> and become available once you sign back in. Your existing
      data is not changed by the upgrade.</span></li>
<?php endif; ?>
</ul>

<form method="post">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(UpdaterAuth::csrfToken()) ?>">
  <input type="hidden" name="action" value="install">
  <button type="submit"><?= $editionChange ? 'Upgrade to IceHRM Pro ' : 'Update to ' ?><?= htmlspecialchars(UpdaterPackage::formatVersion($newVersion)) ?></button>
</form>
