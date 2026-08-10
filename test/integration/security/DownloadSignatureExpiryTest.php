<?php
/**
 * Integration test — signed download URLs expire.
 * ==============================================
 *
 * FileService::getLocalSecureUrl mints unauthenticated download links that skip the
 * login gate. The signature previously covered only the filename, so a captured
 * link was valid forever. It now covers "<filename>|<expires>", and
 * verifyDownloadSignature (used by service.php?a=download) rejects a missing,
 * malformed, past, or tampered link. This pins that contract.
 *
 * Run:  php test/integration/security/DownloadSignatureExpiryTest.php
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;
use Classes\FileService;

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

$fs = FileService::getInstance();
$bs = BaseService::getInstance();
$name = 'report-export-abc123.pdf';

// A correctly-signed link valid for another hour.
$future = time() + 3600;
$goodSig = $bs->createHash($name . '|' . $future);

// A correctly-signed link whose window has passed.
$past = time() - 10;
$expiredSig = $bs->createHash($name . '|' . $past);

fwrite(STDOUT, "FileService::verifyDownloadSignature — expiry enforced\n\n");

verdict(
    "a fresh, correctly-signed link is accepted",
    $fs->verifyDownloadSignature($name, $future, $goodSig) === true
);
verdict(
    "an expired link is rejected even with a valid signature",
    $fs->verifyDownloadSignature($name, $past, $expiredSig) === false
);
verdict(
    "a missing expires is rejected",
    $fs->verifyDownloadSignature($name, null, $goodSig) === false
);
verdict(
    "a non-numeric expires is rejected",
    $fs->verifyDownloadSignature($name, 'soon', $goodSig) === false
);
verdict(
    "a tampered signature is rejected",
    $fs->verifyDownloadSignature($name, $future, $goodSig . 'x') === false
);
verdict(
    "reusing a valid signature with a later expires is rejected",
    $fs->verifyDownloadSignature($name, $future + 5, $goodSig) === false
);
verdict(
    "an array signature is rejected (no type juggling)",
    $fs->verifyDownloadSignature($name, $future, array($goodSig)) === false
);

echo "\n" . str_repeat('=', 64) . "\n";
printf("DOWNLOAD SIGNATURE EXPIRY  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
