<?php
/**
 * The React app shell now lives at {CLIENT_BASE_URL}ui/ (app/ui/index.php).
 * Keep this old path working by redirecting; the browser re-applies any #hash.
 */
header('Location: ui/', true, 302);
exit;
