<?php
require dirname(__FILE__) . "/config.php";

/*
 * Pre-flight environment checks. Each entry: [title, htmlDescription, [commands]].
 * The form is only shown once this list is empty.
 */
$errorMap = array();
$configPath = CLIENT_APP_PATH . "config.php";

// The config file doubles as the install marker: present but empty => fresh install.
$isConfigFileExists = file_exists($configPath);
if ($isConfigFileExists) {
    $data = file_get_contents($configPath);
    if ($data != "") {
        $errorMap[] = array(
            "This application is already installed",
            "A non-empty configuration file already exists. To reinstall, delete <code>" . htmlspecialchars($configPath) . "</code>, clear the <code>data</code> folder and use a fresh database.",
            array(),
        );
    }
} else {
    $f = @fopen($configPath, "w");
    if ($f) {
        fwrite($f, "");
        fclose($f);
    }
}

if (!is_writable($configPath)) {
    $errorMap[] = array(
        "The configuration file is not writable",
        "Give the web server write access to <code>" . htmlspecialchars($configPath) . "</code>.",
        array("touch " . $configPath, "chmod 664 " . $configPath),
    );
}

if (!file_exists(CLIENT_APP_PATH . "config.sample.php")) {
    $errorMap[] = array(
        "The sample configuration file is missing",
        "Expected at <code>" . htmlspecialchars(CLIENT_APP_PATH . "config.sample.php") . "</code>. Restore it from the release package.",
        array(),
    );
}

$dataDir = CLIENT_APP_PATH . "data";
if (!is_dir($dataDir)) {
    $errorMap[] = array(
        "The data directory does not exist",
        "Create <code>" . htmlspecialchars($dataDir) . "</code> and make it writable by the web server.",
        array("mkdir " . $dataDir, "chmod 775 " . $dataDir),
    );
} else {
    $writable = false;
    $testFile = $dataDir . "/.write_test";
    $f = @fopen($testFile, "w");
    if ($f) {
        fwrite($f, "ok");
        fclose($f);
        $writable = (@file_get_contents($testFile) === "ok");
        @unlink($testFile);
    }
    if (!$writable) {
        $errorMap[] = array(
            "The data folder is not writable",
            "Give the web server write access to <code>" . htmlspecialchars($dataDir) . "</code>.",
            array("chmod 775 " . $dataDir),
        );
    }
}

$phpVersionOk = version_compare(PHP_VERSION, '7.3.0', '>=');
if (!$phpVersionOk) {
    $errorMap[] = array(
        "PHP " . PHP_VERSION . " is too old",
        "IceHRM requires PHP 7.3 or newer.",
        array(),
    );
}
if (!extension_loaded('mysqli')) {
    $errorMap[] = array(
        "The PHP <code>mysqli</code> extension is not enabled",
        "Enable the mysqli extension in your PHP configuration and reload this page.",
        array(),
    );
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Install IceHRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="bootstrap/ico/favicon.ico">
    <style>
        :root {
            --bg: #f4f6fb; --card: #ffffff; --ink: #1f2733; --muted: #6b7684;
            --line: #e4e8ef; --accent: #2c6ef2; --accent-ink: #ffffff;
            --ok-bg: #e7f6ec; --ok-ink: #1c7c41; --err-bg: #fdecec; --err-ink: #b42318;
            --code-bg: #f0f2f7; --field-bg: #ffffff;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #10151d; --card: #182029; --ink: #e6ebf2; --muted: #93a0b1;
                --line: #263140; --accent: #4f8cff; --accent-ink: #0a0f16;
                --ok-bg: #14301f; --ok-ink: #67d391; --err-bg: #3a1a1a; --err-ink: #ff9b91;
                --code-bg: #0f151d; --field-bg: #0f151d;
            }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; background: var(--bg); color: var(--ink);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5; padding: 32px 16px;
        }
        .wrap { max-width: 640px; margin: 0 auto; }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .brand .logo {
            width: 40px; height: 40px; border-radius: 10px; background: var(--accent);
            color: var(--accent-ink); display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 20px;
        }
        .brand h1 { font-size: 20px; margin: 0; font-weight: 700; }
        .brand p { margin: 0; font-size: 13px; color: var(--muted); }
        .card {
            background: var(--card); border: 1px solid var(--line); border-radius: 14px;
            padding: 28px; box-shadow: 0 8px 30px rgba(20, 30, 50, 0.06);
        }
        h2 { font-size: 16px; margin: 0 0 6px; font-weight: 700; }
        .lede { color: var(--muted); font-size: 13.5px; margin: 0 0 22px; }
        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .field .hint { display: block; font-size: 12px; color: var(--muted); margin-top: 5px; }
        input[type=text], input[type=password], input[type=email] {
            width: 100%; padding: 10px 12px; font-size: 14px; color: var(--ink);
            background: var(--field-bg); border: 1px solid var(--line); border-radius: 9px;
            outline: none; transition: border-color .15s, box-shadow .15s;
        }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(44, 110, 242, .18); }
        /* Keep browser autofill from forcing a white background in dark mode. */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: var(--ink);
            -webkit-box-shadow: 0 0 0 1000px var(--field-bg) inset;
            box-shadow: 0 0 0 1000px var(--field-bg) inset;
            caret-color: var(--ink);
        }
        .row { display: flex; gap: 12px; flex-wrap: wrap; }
        .row .field { flex: 1 1 200px; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 22px; }
        button {
            font: inherit; font-weight: 600; padding: 10px 18px; border-radius: 9px;
            border: 1px solid var(--line); background: var(--card); color: var(--ink);
            cursor: pointer; transition: background .15s, opacity .15s;
        }
        button:hover:not(:disabled) { background: var(--code-bg); }
        button.primary { background: var(--accent); color: var(--accent-ink); border-color: var(--accent); }
        button.primary:hover:not(:disabled) { opacity: .92; background: var(--accent); }
        button:disabled { opacity: .5; cursor: not-allowed; }
        .msg { margin-top: 18px; padding: 12px 14px; border-radius: 9px; font-size: 13.5px; display: none; }
        .msg.ok { background: var(--ok-bg); color: var(--ok-ink); display: block; }
        .msg.err { background: var(--err-bg); color: var(--err-ink); display: block; }
        .spinner {
            display: inline-block; width: 14px; height: 14px; margin-right: 7px; vertical-align: -2px;
            border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        code {
            background: var(--code-bg); padding: 2px 6px; border-radius: 5px;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px;
        }
        .precheck { margin-bottom: 16px; padding: 16px; border: 1px solid var(--err-ink);
            border-radius: 10px; background: var(--err-bg); }
        .precheck h3 { margin: 0 0 4px; font-size: 14px; color: var(--err-ink); }
        .precheck .desc { font-size: 13px; margin-bottom: 8px; }
        .precheck pre { margin: 4px 0 0; padding: 8px 10px; background: var(--code-bg);
            border-radius: 7px; font-size: 12.5px; overflow-x: auto; }
        .foot { text-align: center; color: var(--muted); font-size: 11.5px; margin-top: 18px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <div class="logo">Ice</div>
        <div>
            <h1>Install IceHRM</h1>
            <p>Set up your database and finish installation.</p>
        </div>
    </div>

    <div class="card">
        <?php if (count($errorMap) > 0) { ?>
            <h2>Fix these before continuing</h2>
            <p class="lede">The environment isn&rsquo;t ready yet. Resolve the items below, then reload.</p>
            <?php foreach ($errorMap as $error) { ?>
                <div class="precheck">
                    <h3><?php echo $error[0]; ?></h3>
                    <div class="desc"><?php echo $error[1]; ?></div>
                    <?php if (!empty($error[2]) && is_array($error[2])) { ?>
                        <pre><?php echo htmlspecialchars(implode("\n", $error[2])); ?></pre>
                    <?php } ?>
                </div>
            <?php } ?>
            <div class="actions">
                <button class="primary" onclick="location.reload(); return false;">Reload and re-check</button>
            </div>
        <?php } else { ?>
            <h2>Database &amp; application settings</h2>
            <p class="lede">Point IceHRM at an <strong>empty</strong> MySQL database. Test the connection, then install.</p>

            <div class="field">
                <label for="BASE_URL">Application URL</label>
                <input type="text" id="BASE_URL" value="">
                <span class="hint">Public URL of the folder IceHRM is served from, e.g. <code>https://hr.example.com/</code></span>
            </div>

            <div class="row">
                <div class="field">
                    <label for="APP_HOST">Database host</label>
                    <input type="text" id="APP_HOST" value="localhost">
                </div>
                <div class="field">
                    <label for="APP_DB">Database name</label>
                    <input type="text" id="APP_DB" value="icehrmdb">
                </div>
            </div>

            <div class="row">
                <div class="field">
                    <label for="APP_USERNAME">Database user</label>
                    <input type="text" id="APP_USERNAME" value="icehrmuser">
                </div>
                <div class="field">
                    <label for="APP_PASSWORD">Database password</label>
                    <input type="password" id="APP_PASSWORD" value="" autocomplete="new-password">
                </div>
            </div>

            <h2 style="margin-top: 26px;">Administrator account</h2>
            <p class="lede">Your first admin sign-in. You can change these later in the app.</p>
            <div class="field">
                <label for="ADMIN_EMAIL">Admin email</label>
                <input type="email" id="ADMIN_EMAIL" value="" autocomplete="email">
                <span class="hint">Sign in with this email or the username <code>admin</code>.</span>
            </div>
            <div class="row">
                <div class="field">
                    <label for="ADMIN_PASSWORD">Admin password</label>
                    <input type="password" id="ADMIN_PASSWORD" value="" autocomplete="new-password">
                    <span class="hint">At least 6 characters.</span>
                </div>
                <div class="field">
                    <label for="ADMIN_PASSWORD2">Confirm password</label>
                    <input type="password" id="ADMIN_PASSWORD2" value="" autocomplete="new-password">
                </div>
            </div>

            <div class="field">
                <label for="LOG">Log file path</label>
                <input type="text" id="LOG" value="data/icehrm.log">
                <span class="hint">Leave empty to use the web server&rsquo;s default log.</span>
            </div>

            <div class="actions">
                <button id="testBtn" onclick="testDB(); return false;">Test connection</button>
                <button id="installBtn" class="primary" onclick="install(); return false;" disabled>Install IceHRM</button>
            </div>

            <div id="msg" class="msg"></div>
        <?php } ?>
    </div>

    <div class="foot"><?php echo htmlspecialchars(APP_NAME); ?> &middot; installer</div>
</div>

<script>
    (function () {
        var el = document.getElementById('BASE_URL');
        if (!el) return;
        var u = window.location.href;
        var i = u.indexOf('/app/install');
        if (i > -1) { u = u.substring(0, i); }
        el.value = u + '/';
    })();

    function val(id) { var e = document.getElementById(id); return e ? e.value : ''; }

    function showMsg(type, text) {
        var m = document.getElementById('msg');
        if (!m) return;
        m.className = 'msg ' + (type === 'ok' ? 'ok' : 'err');
        m.innerHTML = text;
    }

    function busy(btn, on, label) {
        btn.disabled = on;
        if (on) {
            btn.dataset.label = btn.innerHTML;
            btn.innerHTML = '<span class="spinner"></span>' + label;
        } else if (btn.dataset.label) {
            btn.innerHTML = btn.dataset.label;
        }
    }

    function post(data) {
        var body = Object.keys(data).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
        }).join('&');
        return fetch('submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        }).then(function (r) { return r.json(); });
    }

    function dbFields() {
        return {
            APP_DB: val('APP_DB'),
            APP_USERNAME: val('APP_USERNAME'),
            APP_PASSWORD: val('APP_PASSWORD'),
            APP_HOST: val('APP_HOST')
        };
    }

    function testDB() {
        var btn = document.getElementById('testBtn');
        var req = dbFields();
        req.action = 'TEST_DB';
        busy(btn, true, 'Testing…');
        post(req).then(function (data) {
            busy(btn, false);
            if (data.status === 'SUCCESS') {
                showMsg('ok', data.msg);
                document.getElementById('installBtn').disabled = false;
            } else {
                showMsg('err', data.msg);
                document.getElementById('installBtn').disabled = true;
            }
        }).catch(function (e) {
            busy(btn, false);
            showMsg('err', 'Request failed: ' + e);
        });
    }

    function install() {
        var btn = document.getElementById('installBtn');
        var base = val('BASE_URL');
        if (!base || (base.indexOf('http://') !== 0 && base.indexOf('https://') !== 0)) {
            showMsg('err', 'Please enter a valid Application URL starting with http:// or https://');
            return;
        }
        if (base.charAt(base.length - 1) !== '/') { base += '/'; }

        var adminEmail = val('ADMIN_EMAIL').trim();
        var adminPw = val('ADMIN_PASSWORD');
        var adminPw2 = val('ADMIN_PASSWORD2');
        if (!adminEmail || adminEmail.indexOf('@') < 1 || adminEmail.lastIndexOf('.') < adminEmail.indexOf('@')) {
            showMsg('err', 'Please enter a valid admin email address.');
            return;
        }
        if (adminPw.length < 6) {
            showMsg('err', 'The admin password must be at least 6 characters.');
            return;
        }
        if (adminPw !== adminPw2) {
            showMsg('err', 'The admin passwords do not match.');
            return;
        }

        var req = dbFields();
        req.action = 'INS';
        req.LOG = val('LOG');
        req.BASE_URL = base;
        req.ADMIN_EMAIL = adminEmail;
        req.ADMIN_PASSWORD = adminPw;

        busy(btn, true, 'Installing…');
        showMsg('ok', 'Creating the database schema and writing configuration…');
        post(req).then(function (data) {
            if (data.status === 'SUCCESS') {
                showMsg('ok', data.msg + ' Redirecting…');
                window.top.location.href = base + 'app/';
            } else {
                busy(btn, false);
                showMsg('err', data.msg);
            }
        }).catch(function (e) {
            busy(btn, false);
            showMsg('err', 'Request failed: ' + e);
        });
    }
</script>
</body>
</html>
