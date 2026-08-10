<?php
/**
 * Preflight results, then where to get the new version from.
 *
 * Pro installations paste a signed download link (they are licensed per customer);
 * the free edition has a single published location and nothing to choose.
 */
?>
<?php if (!empty($error)): ?>
  <div class="msg error"><?= $error ?></div>
<?php endif; ?>
<?php if (!empty($notice)): ?>
  <div class="msg ok"><?= htmlspecialchars($notice) ?></div>
<?php endif; ?>

<h2>Before updating</h2>
<ul class="checks">
  <?php foreach ($checks as $check): ?>
    <li>
      <span class="mark <?= $check['ok'] ? 'ok' : 'bad' ?>"><?= $check['ok'] ? '&#10003;' : '&#10007;' ?></span>
      <span>
        <strong><?= htmlspecialchars($check['label']) ?></strong><br>
        <span class="detail"><?= $check['detail'] ?></span>
        <?php if (!$check['ok'] && !empty($check['command'])): ?>
          <pre><?= htmlspecialchars($check['command']) ?></pre>
        <?php endif; ?>
      </span>
    </li>
  <?php endforeach; ?>
</ul>

<?php if (!$ready): ?>
  <div class="msg warn" style="margin-top:18px">
    Fix the items above, then reload this page. The update cannot start until they all pass.
  </div>
<?php else: ?>
  <h2>Download the new version</h2>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(UpdaterAuth::csrfToken()) ?>">
    <input type="hidden" name="action" value="download">
    <?php if (UpdaterBootstrap::isPro()): ?>
      <p class="detail">Paste the download link for the new IceHRM Pro release. Links from
      your IceHRM account are time-limited, so copy it immediately before using it here.</p>
      <label for="url">IceHRM Pro download link</label>
      <input type="url" id="url" name="url" placeholder="https://…/icehrmpro.zip" required autofocus>
      <p class="detail" style="margin-top:8px">
        Allowed sources: <?= htmlspecialchars(implode(', ', UpdaterPackage::allowedHostsForDisplay())) ?>
      </p>
      <?php if (!empty(UpdaterPackage::extraHosts())): ?>
        <div class="msg warn" style="margin-top:10px">
          This installation permits downloads from
          <code><?= htmlspecialchars(implode('</code>, <code>', UpdaterPackage::extraHosts())) ?></code>
          with certificate checking disabled, because
          <code><?= htmlspecialchars(UpdaterPackage::EXTRA_HOSTS_FILE) ?></code> is present at the
          installation root. That is intended for release testing only — remove the file on a
          production system.
        </div>
      <?php endif; ?>
    <?php else: ?>
      <p class="detail">The latest IceHRM release will be downloaded from:</p>
      <pre><?= htmlspecialchars(UpdaterPackage::FREE_URL) ?></pre>
    <?php endif; ?>
    <button type="submit">Download and check version</button>
  </form>
  <p class="detail" style="margin-top:14px">
    Nothing is changed by this step. The release is downloaded to <code>updater/data/</code>
    and its version compared with yours; you will be asked to confirm before any file is replaced.
  </p>
<?php endif; ?>
