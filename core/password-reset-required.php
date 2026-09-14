<?php
/**
 * Forced password reset (legacy MD5 accounts).
 *
 * Included by spa-shell.php INSTEAD of the app shell when
 * PasswordManager::userNeedsPasswordReset() holds. The account authenticated against an
 * unsalted MD5 hash, which a database leak would hand straight to an attacker, so the
 * password must be upgraded to bcrypt before anything else is served.
 *
 * This page is deliberately self-contained: no app bundle, no JWT, no module data. The
 * only thing it can do is POST service.php?a=fpc. On success the session is destroyed
 * server-side and the browser is sent back to login.php to sign in with the new
 * password.
 *
 * Expects (from spa-shell.php): $user, CLIENT_BASE_URL, BASE_URL.
 */

$fpcCsrf = \Classes\BaseService::getInstance()->generateCsrf('fpc');

$companyName = \Classes\SettingsManager::getInstance()->getSetting('Company: Name');
if (empty($companyName) || $companyName === 'Sample Company Pvt Ltd') {
    $companyName = 'IceHrm';
}
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($companyName, ENT_QUOTES) ?> — Update your password</title>
    <link rel="shortcut icon" href="https://icehrm.s3.amazonaws.com/images/icon16.png">
    <style>
        :root { --accent:#2f6fed; --err:#c0392b; --ok:#1e8e5a; --ink:#1f2733; --muted:#6b7684; }
        * { box-sizing:border-box; }
        body {
            margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
            background:#eef1f6; padding:24px;
            font-family:'Roboto',-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;
            color:var(--ink);
        }
        .card { background:#fff; width:100%; max-width:440px; border-radius:10px; padding:32px;
                box-shadow:0 6px 28px rgba(20,30,50,.12); }
        h1 { margin:0 0 6px; font-size:20px; font-weight:500; }
        .sub { margin:0 0 22px; color:var(--muted); font-size:14px; line-height:1.5; }
        label { display:block; font-size:13px; margin:14px 0 5px; color:var(--muted); }
        input[type=password] {
            width:100%; padding:10px 12px; border:1px solid #d4dae3; border-radius:6px;
            font-size:14px; background:#fff; color:var(--ink);
        }
        input[type=password]:focus { outline:none; border-color:var(--accent); }
        button {
            width:100%; margin-top:22px; padding:11px; border:0; border-radius:6px;
            background:var(--accent); color:#fff; font-size:15px; cursor:pointer;
        }
        button:disabled { opacity:.6; cursor:default; }
        .msg { margin-top:16px; font-size:13px; display:none; line-height:1.5; }
        .msg.error { color:var(--err); display:block; }
        .msg.ok { color:var(--ok); display:block; }
        .who { font-size:13px; color:var(--muted); margin-bottom:18px; }
        .who b { color:var(--ink); font-weight:500; }
    </style>
</head>
<body>
<div class="card">
    <h1>Update your password</h1>
    <p class="sub">
        Your password is stored using an outdated format that is no longer considered
        secure. Please choose a new password to continue — you will be asked to sign in
        again afterwards.
    </p>
    <p class="who">Signed in as <b><?= htmlspecialchars($user->username, ENT_QUOTES) ?></b></p>

    <form id="fpcForm" autocomplete="off">
        <input type="hidden" id="csrf" value="<?= htmlspecialchars($fpcCsrf, ENT_QUOTES) ?>">
        <label for="current">Current password</label>
        <input type="password" id="current" autocomplete="current-password" required>

        <label for="pwd">New password</label>
        <input type="password" id="pwd" autocomplete="new-password" required>

        <label for="confirm">Confirm new password</label>
        <input type="password" id="confirm" autocomplete="new-password" required>

        <button type="submit" id="submitBtn">Update password</button>
        <div class="msg" id="msg"></div>
    </form>
</div>

<script>
(function () {
    var form = document.getElementById('fpcForm');
    var btn = document.getElementById('submitBtn');
    var msg = document.getElementById('msg');

    function show(text, cls) {
        msg.textContent = text;          // textContent: never render server text as HTML
        msg.className = 'msg ' + cls;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var current = document.getElementById('current').value;
        var pwd = document.getElementById('pwd').value;
        var confirm = document.getElementById('confirm').value;

        if (pwd !== confirm) {
            show('The new passwords do not match.', 'error');
            return;
        }

        btn.disabled = true;
        show('Updating…', 'ok');

        var body = new URLSearchParams({
            a: 'fpc',
            csrf: document.getElementById('csrf').value,
            current: current,
            pwd: pwd
        });

        fetch('<?= CLIENT_BASE_URL ?>service.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data && data.status === 'SUCCESS') {
                show('Password updated. Redirecting you to sign in…', 'ok');
                setTimeout(function () {
                    window.location.href = '<?= CLIENT_BASE_URL ?>login.php';
                }, 1200);
            } else {
                btn.disabled = false;
                show((data && data.message) ? data.message : 'Could not update the password.', 'error');
            }
        })
        .catch(function () {
            btn.disabled = false;
            show('Could not reach the server. Please try again.', 'error');
        });
    });
})();
</script>
</body>
</html>
