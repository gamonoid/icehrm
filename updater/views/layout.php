<?php
/**
 * The one page shell every step renders into.
 *
 * No external assets: after core/ and web/ have been moved aside, anything loaded from
 * the application would 404 — and the screen reporting a failed update is exactly the
 * screen that must still render. Everything here is inline.
 *
 * Expects: $title, $content (already-escaped HTML), optionally $user.
 */
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> — IceHRM Updater</title>
<style>
  :root { --line:#e3e8ef; --ink:#1f2933; --muted:#6b7785; --ok:#0b8a4b; --bad:#c0392b; --accent:#2b6cb0; }
  * { box-sizing:border-box; }
  body { margin:0; padding:32px 16px; background:#f4f6f8; color:var(--ink);
         font:15px/1.55 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; }
  .wrap { max-width:760px; margin:0 auto; }
  .card { background:#fff; border:1px solid var(--line); border-radius:8px; padding:28px; }
  h1 { margin:0 0 4px; font-size:20px; }
  h2 { font-size:15px; margin:24px 0 8px; }
  .sub { color:var(--muted); margin:0 0 22px; font-size:13px; }
  label { display:block; font-weight:600; margin:14px 0 5px; font-size:13px; }
  input[type=text], input[type=password], input[type=url] {
      width:100%; padding:9px 11px; border:1px solid #cbd2d9; border-radius:5px; font-size:14px; }
  button { margin-top:18px; padding:10px 18px; border:0; border-radius:5px; background:var(--accent);
           color:#fff; font-size:14px; font-weight:600; cursor:pointer; }
  button.secondary { background:#e4e7eb; color:var(--ink); }
  button.danger { background:var(--bad); }
  .msg { padding:11px 13px; border-radius:5px; margin:0 0 18px; font-size:14px; }
  .msg.error { background:#fdecea; border:1px solid #f5c6c2; color:#8a2119; }
  .msg.ok { background:#e8f6ee; border:1px solid #b7e0c8; color:#0b5c35; }
  .msg.warn { background:#fff8e5; border:1px solid #f2dfab; color:#7a5b06; }
  pre { background:#1f2933; color:#e6edf3; padding:12px 14px; border-radius:5px;
        overflow-x:auto; font-size:12.5px; line-height:1.5; }
  ul.checks { list-style:none; padding:0; margin:0; }
  ul.checks li { padding:9px 0; border-bottom:1px solid var(--line); display:flex; gap:10px; }
  ul.checks li:last-child { border-bottom:0; }
  .mark { flex:0 0 18px; font-weight:700; }
  .mark.ok { color:var(--ok); } .mark.bad { color:var(--bad); }
  .detail { color:var(--muted); font-size:13px; }
  .meta { display:flex; justify-content:space-between; align-items:center;
          border-top:1px solid var(--line); margin-top:26px; padding-top:14px;
          color:var(--muted); font-size:12.5px; }
  a { color:var(--accent); }
  code { background:#eef1f5; padding:1px 5px; border-radius:3px; font-size:13px; }
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p class="sub">IceHRM Updater<?php if (!empty($user)): ?> — signed in as <?= htmlspecialchars($user['username']) ?><?php endif; ?></p>
    <?= $content ?>
    <div class="meta">
      <span>Installed version <?= htmlspecialchars(UpdaterPackage::formatVersion(UpdaterBootstrap::currentVersion())) ?><?= UpdaterBootstrap::isPro() ? ' (Pro)' : '' ?></span>
      <?php if (!empty($user)): ?><span><a href="?action=logout">Sign out</a></span><?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
