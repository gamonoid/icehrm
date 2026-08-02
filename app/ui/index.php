<?php
/**
 * Public entry for the React app shell (SPA migration).
 * Thin wrapper, like app/spa.php / app/api/index.php: load config, delegate to
 * core/spa-shell.php.
 * URL: {CLIENT_BASE_URL}ui/
 */
include __DIR__ . '/../config.php';
include APP_BASE_PATH . 'spa-shell.php';
