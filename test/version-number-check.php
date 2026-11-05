<?php
/**
 * Release guard — VERSION_NUMBER must track VERSION.
 * ==================================================
 *
 * The built-in updater decides whether a downloaded release is newer by comparing the
 * numeric VERSION_NUMBER ('360001'), not the dotted VERSION ('36.0.1'). v36.0.1 shipped
 * with VERSION_NUMBER still at 360000, so an installation on 36.0.0 was told it was
 * "Already up to date" and could not take the security patch through the updater.
 *
 * This check fails the release build when the two drift apart. VERSION_NUMBER is
 * MMmmpp: major, minor and patch each as two digits (36.0.1 -> 360001), which is also
 * the form updater/lib/Package.php::formatVersion() reads back.
 *
 * Run:  php test/version-number-check.php          (no database needed)
 */

$config = dirname(__DIR__) . '/core/config.base.php';
$contents = (string) @file_get_contents($config);

if (!preg_match("/define\s*\(\s*'VERSION'\s*,\s*'([0-9]+)\.([0-9]+)\.([0-9]+)'\s*\)/", $contents, $v)) {
    fwrite(STDERR, "FAIL  could not read a dotted VERSION from $config\n");
    exit(1);
}
if (!preg_match("/define\s*\(\s*'VERSION_NUMBER'\s*,\s*'([0-9]+)'\s*\)/", $contents, $n)) {
    fwrite(STDERR, "FAIL  could not read VERSION_NUMBER from $config\n");
    exit(1);
}

$version = "{$v[1]}.{$v[2]}.{$v[3]}";
$expected = sprintf('%02d%02d%02d', $v[1], $v[2], $v[3]);
$actual = $n[1];

if ($actual !== $expected) {
    fwrite(STDERR, "FAIL  VERSION is $version so VERSION_NUMBER must be '$expected', found '$actual'.\n");
    fwrite(STDERR, "      The updater compares VERSION_NUMBER numerically; a patch release that keeps the\n");
    fwrite(STDERR, "      previous number is reported as 'Already up to date' and never installs.\n");
    exit(1);
}

echo "PASS  VERSION $version <-> VERSION_NUMBER $actual\n";
exit(0);
