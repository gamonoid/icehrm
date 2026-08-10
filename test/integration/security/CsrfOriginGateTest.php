<?php
/**
 * Integration test — the same-origin CSRF gate.
 * ============================================
 *
 * service.php gates state-changing actions (save/add/delete/setAdminEmp/ca) on
 * BaseService::isSameOriginRequest(): the SPA calls them via same-origin XHR (which
 * carries an Origin or a same-origin Referer), while a forged cross-site request
 * carries a foreign Origin/Referer or none. This pins the decision across the cases.
 *
 * Run:  php test/integration/security/CsrfOriginGateTest.php
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

$bs = BaseService::getInstance();
$host = parse_url(CLIENT_BASE_URL, PHP_URL_HOST);
$scheme = parse_url(CLIENT_BASE_URL, PHP_URL_SCHEME);
if (empty($scheme)) { $scheme = 'http'; }
$sameOrigin = $scheme . '://' . $host;
$evil = 'https://attacker.example.com';

function reset_headers()
{
    unset($_SERVER['HTTP_ORIGIN'], $_SERVER['HTTP_REFERER']);
}

fwrite(STDOUT, "BaseService::isSameOriginRequest — CSRF gate (app host: $host)\n\n");

reset_headers();
$_SERVER['HTTP_ORIGIN'] = $sameOrigin;
verdict('same-origin Origin header is allowed', $bs->isSameOriginRequest() === true);

reset_headers();
$_SERVER['HTTP_ORIGIN'] = $evil;
verdict('cross-origin Origin header is refused', $bs->isSameOriginRequest() === false);

reset_headers();
$_SERVER['HTTP_REFERER'] = $sameOrigin . '/app/index.php#tab';
verdict('same-origin Referer (no Origin) is allowed', $bs->isSameOriginRequest() === true);

reset_headers();
$_SERVER['HTTP_REFERER'] = $evil . '/phish.html';
verdict('cross-origin Referer (no Origin) is refused', $bs->isSameOriginRequest() === false);

reset_headers();
verdict('no Origin and no Referer is refused (bare navigation)', $bs->isSameOriginRequest() === false);

// Origin takes precedence; a valid Origin wins even with a foreign Referer, and a
// foreign Origin loses even with a same-origin Referer (Origin is the stronger signal).
reset_headers();
$_SERVER['HTTP_ORIGIN'] = $evil;
$_SERVER['HTTP_REFERER'] = $sameOrigin . '/app/';
verdict('foreign Origin is refused even with a same-origin Referer', $bs->isSameOriginRequest() === false);

reset_headers();

echo "\n" . str_repeat('=', 64) . "\n";
printf("CSRF ORIGIN GATE  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
