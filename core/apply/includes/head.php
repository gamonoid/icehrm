<?php
/**
 * Shared <head> + top bar for the public careers pages (list + single job).
 * Expects: $meta (title/description/url/imageUrl), $companyName, $logoFileUrl.
 */
$assetBase = BASE_URL.'apply/';
$e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=$e($meta->title)?></title>
    <?php if (!empty($meta->description)) { ?>
    <meta name="description" content="<?=$e($meta->description)?>">
    <?php } ?>
    <!-- Open Graph / social preview -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?=$e($meta->title)?>">
    <?php if (!empty($meta->description)) { ?>
    <meta property="og:description" content="<?=$e($meta->description)?>">
    <?php } ?>
    <?php if (!empty($meta->url)) { ?>
    <meta property="og:url" content="<?=$e($meta->url)?>">
    <?php } ?>
    <?php if (!empty($meta->imageUrl)) { ?>
    <meta property="og:image" content="<?=$e($meta->imageUrl)?>">
    <meta name="twitter:card" content="summary_large_image">
    <?php } ?>
    <link rel="shortcut icon" href="<?=$assetBase?>image/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="<?=$e(BASE_URL)?>">
    <link rel="stylesheet" href="<?=$assetBase?>css/careers.css">
</head>
<body>
<header class="site-header">
    <div class="wrap">
        <a class="brand" href="<?=CLIENT_BASE_URL.'apply/'?>">
            <?php if (!empty($logoFileUrl)) { ?><img src="<?=$e($logoFileUrl)?>" alt="<?=$e($companyName)?>"><?php } ?>
            <span class="name"><?=$e($companyName)?></span>
        </a>
        <span class="spacer"></span>
        <a class="btn btn-ghost" href="<?=CLIENT_BASE_URL.'apply/'?>">All openings</a>
    </div>
</header>
