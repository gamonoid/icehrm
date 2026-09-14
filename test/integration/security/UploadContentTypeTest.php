<?php
/**
 * Integration test — upload content-type (magic-byte) check.
 * =========================================================
 *
 * The upload handlers validate only the client-supplied extension.
 * iceUploadContentAllowed() adds a magic-byte check so a non-image file disguised
 * with an image extension (HTML/SVG named .png/.jpg, which could render inline) is
 * rejected, while a genuine raster image and any non-image type are accepted.
 *
 * Run:  php test/integration/security/UploadContentTypeTest.php
 */

require __DIR__ . '/../bootstrap.php';

// upload.auth.inc.php ends with an auth block that exits unless a valid $user is set.
$user = new stdClass();
$user->id = 1; $user->email = 'x@example.com'; $user->user_level = 'Admin';
require_once dirname(__DIR__, 2) . '/../core/upload.auth.inc.php';

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

function tmpWith($bytes)
{
    $p = tempnam(sys_get_temp_dir(), 'iceup_');
    file_put_contents($p, $bytes);
    return $p;
}

// Genuine, well-formed 1x1 images (getimagesize must read real headers/dimensions).
$png = base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQAY3Y2wAAAAAElFTkSuQmCC'
);
$jpeg = base64_decode(
    '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEB'
    . 'AQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEB'
    . 'AQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAA'
    . 'AAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AsMAf/9k='
);
$gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
$html = "<html><body><script>alert(document.cookie)</script></body></html>";
$svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';

fwrite(STDOUT, "iceUploadContentAllowed — magic-byte check\n\n");

$paths = array();
$mk = function ($bytes) use (&$paths) { $p = tmpWith($bytes); $paths[] = $p; return $p; };

verdict('genuine PNG with .png is allowed', iceUploadContentAllowed($mk($png), 'png') === true);
verdict('genuine JPEG with .jpg is allowed', iceUploadContentAllowed($mk($jpeg), 'jpg') === true);
verdict('genuine GIF with .gif is allowed', iceUploadContentAllowed($mk($gif), 'gif') === true);

verdict('HTML disguised as .png is REJECTED', iceUploadContentAllowed($mk($html), 'png') === false);
verdict('HTML disguised as .jpg is REJECTED', iceUploadContentAllowed($mk($html), 'jpg') === false);
verdict('SVG disguised as .png is REJECTED', iceUploadContentAllowed($mk($svg), 'png') === false);

// Non-image extensions are download-only and not content-restricted here.
verdict('HTML bytes with .pdf extension are allowed (download-only type)',
    iceUploadContentAllowed($mk($html), 'pdf') === true);
verdict('text with .csv extension is allowed', iceUploadContentAllowed($mk("a,b,c\n1,2,3"), 'csv') === true);

foreach ($paths as $p) { @unlink($p); }

echo "\n" . str_repeat('=', 64) . "\n";
printf("UPLOAD CONTENT TYPE  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
