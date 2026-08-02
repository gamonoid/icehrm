<?php
/**
 * Hash-protected direct access to internal HR resources/actions — no login.
 *
 * URL: <CLIENT_BASE_URL>secure/?resource=<resource_id>&hash=<secure_hash>
 *
 * The resource row (SecureResources) names a handler_class — a fully-qualified
 * class in core or any extension — so the FULL application (all core modules +
 * all extensions) is bootstrapped below before the handler runs. The handler's
 * handle() method decides what the visitor sees.
 */

use Classes\SecureResourceService;

// Full application bootstrap (DB, BaseService, all modules + extensions,
// email sender, notification manager). Works without a logged-in user —
// the same bootstrap used by other anonymous pages (accept-invitation).
include 'includes.inc.php';

$renderError = function ($title, $message) {
    http_response_code(403);
    $logoUrl = '';
    try {
        $logoUrl = \Classes\UIManager::getInstance()->getCompanyLogoUrl();
    } catch (\Exception $e) {
        // best effort
    }
    ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?php echo htmlspecialchars($title); ?></title>
<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
           background: #f0f2f5; margin: 0; display: flex; align-items: center;
           justify-content: center; min-height: 100vh; }
    .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.09);
            padding: 40px; max-width: 420px; text-align: center; }
    h1 { font-size: 20px; color: #262626; margin: 16px 0 8px; }
    p { color: #8c8c8c; font-size: 14px; line-height: 1.6; margin: 0; }
    img { max-height: 48px; }
</style>
</head>
<body>
    <div class="card">
        <?php if (!empty($logoUrl)) { ?><img src="<?php echo htmlspecialchars($logoUrl); ?>" alt=""/><?php } ?>
        <h1><?php echo htmlspecialchars($title); ?></h1>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
</body>
</html>
    <?php
    exit;
};

$resourceId = isset($_REQUEST['resource']) ? $_REQUEST['resource'] : '';
$hash = isset($_REQUEST['hash']) ? $_REQUEST['hash'] : '';

$resource = SecureResourceService::getInstance()->verify($resourceId, $hash);
if (empty($resource)) {
    $renderError(
        'This link is not valid',
        'It may have expired or been disabled. Please contact the person who sent it to you.'
    );
}

$handlerClass = $resource->handler_class;
if (empty($handlerClass) || !class_exists($handlerClass)) {
    \Utils\LogManager::getInstance()->error('Secure resource handler not found: ' . $handlerClass);
    $renderError(
        'This link is not available',
        'The requested resource cannot be served right now. Please contact your HR department.'
    );
}

try {
    $handler = new $handlerClass();
    $handler->handle($resource);
} catch (\Exception $e) {
    \Utils\LogManager::getInstance()->error('Secure resource handler error: ' . $e->getMessage());
    $renderError(
        'Something went wrong',
        'The requested resource cannot be served right now. Please contact your HR department.'
    );
}
