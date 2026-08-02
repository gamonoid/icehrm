<?php
/**
 * Shared footer for the public careers pages.
 * Expects: $companyName, and $slashFix (set by the including page).
 */
$e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$slashFix = isset($slashFix) ? $slashFix : (defined('IS_CLOUD') && IS_CLOUD ? '' : '/');
?>
<footer class="site-footer">
    <div class="wrap">
        <div>
            <h2><?=$e($companyName)?></h2>
            <p>Built with &#10084;&#65039; using <a href="https://icehrm.com" rel="noopener">IceHrm</a></p>
        </div>
        <a class="btn btn-primary btn-lg" href="<?=CLIENT_BASE_URL.'apply'.$slashFix?>">View all openings</a>
    </div>
</footer>
</body>
</html>
