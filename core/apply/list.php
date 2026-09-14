<?php
/**
 * Public careers board — lists all active job openings.
 * Data prepared in apply/index.php: $jobsArr, $companyName, $companyDescription,
 * $logoFileUrl, $meta, and the *_Name field aliases.
 */
$slashFix = IS_CLOUD ? '' : '/';
$e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$jobUrl = function ($code) use ($slashFix) {
    return CLIENT_BASE_URL.'apply'.$slashFix.'?ref='.urlencode($code);
};
include __DIR__.'/includes/head.php';
?>
<main class="wrap">
    <section class="hero">
        <?php if (!empty($logoFileUrl)) { ?><img src="<?=$e($logoFileUrl)?>" alt="<?=$e($companyName)?>"><?php } ?>
        <h1>Join <?=$e($companyName)?></h1>
        <?php
        // Ignore the default demo placeholder so it never shows on a real board.
        $heroTagline = trim((string)$companyDescription);
        if ($heroTagline === '' || stripos($heroTagline, 'This is a company using icehrm.com') !== false) {
            $heroTagline = 'Explore our open positions and find the role that fits you.';
        }
        ?>
        <p><?=$e($heroTagline)?></p>
        <?php $jobCount = count($jobsArr); ?>
        <span class="count"><?=$jobCount?> open <?=$jobCount === 1 ? 'position' : 'positions'?></span>
    </section>

    <?php if (empty($jobsArr)) { ?>
        <div class="empty">
            <div class="ic">&#128188;</div>
            <h3>No open positions right now</h3>
            <p>We're not hiring at the moment &mdash; please check back soon.</p>
        </div>
    <?php } else { ?>
        <section class="jobs">
            <?php foreach ($jobsArr as $job) {
                $func = $jobFunctionName;
                $etype = $employementTypeName;
                $loc = $job->location ? $job->location : $job->country_Name;
            ?>
                <a class="job-card" href="<?=$e($jobUrl($job->code))?>">
                    <div class="top">
                        <div>
                            <h3><?=$e($job->title)?></h3>
                            <div class="company"><?=$e($job->companyName)?></div>
                        </div>
                        <?php if (!empty($job->$func)) { ?>
                            <span class="func"><?=$e($job->$func)?></span>
                        <?php } ?>
                    </div>
                    <?php if (!empty($job->shortDescription)) { ?>
                        <div class="desc"><?=$e($job->shortDescription)?></div>
                    <?php } ?>
                    <div class="chips">
                        <?php if (!empty($loc)) { ?>
                            <span class="chip"><span class="ic">&#128205;</span><?=$e($loc)?></span>
                        <?php } ?>
                        <?php if (!empty($job->$etype)) { ?>
                            <span class="chip"><span class="ic">&#9203;</span><?=$e($job->$etype)?></span>
                        <?php } ?>
                        <?php if (!empty($job->closingDate)) { ?>
                            <span class="chip"><span class="ic">&#128197;</span>Closes <?=$e(date('M j, Y', strtotime($job->closingDate)))?></span>
                        <?php } ?>
                    </div>
                </a>
            <?php } ?>
        </section>
    <?php } ?>
</main>
<?php include __DIR__.'/includes/foot.php'; ?>
