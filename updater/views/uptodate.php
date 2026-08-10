<?php /** The downloaded release is not newer than what is installed. */ ?>
<div class="msg ok">
  This installation is already up to date.
</div>
<p>
  Installed version: <strong><?= htmlspecialchars(UpdaterPackage::formatVersion($currentVersion)) ?></strong><br>
  Downloaded release: <strong><?= htmlspecialchars(UpdaterPackage::formatVersion($newVersion)) ?></strong>
</p>
<p class="detail">
  The downloaded release is not newer than the version you are running, so nothing has been
  changed. If you expected a newer version, check that you used the correct download link.
</p>
<form method="post">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(UpdaterAuth::csrfToken()) ?>">
  <button class="secondary" type="submit">Back</button>
</form>
