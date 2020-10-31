<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="js/jquery.sidebar/css/codebrowser/sidebar.css" />
        <link rel="stylesheet" type="text/css" href="css/cruisecontrol.css" />
        <link rel="stylesheet" type="text/css" href="css/global.css" />
        <link rel="stylesheet" type="text/css" href="css/review.css" />
        <link rel="stylesheet" type="text/css" href="css/tree.css" />

        <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.jstree/jquery.jstree.min.js"></script>
        <script type="text/javascript" src="js/jquery.sidebar/jquery-ui-1.7.2.custom.min.js"></script>
        <script type="text/javascript" src="js/jquery.sidebar/jquery.sidebar.js"></script>
        <script type="text/javascript" src="js/jquery.cluetip/lib/jquery.hoverIntent.js"></script>
        <script type="text/javascript" src="js/jquery.cluetip/lib/jquery.bgiframe.min.js"></script>
        <script type="text/javascript" src="js/jquery.cluetip/jquery.cluetip.min.js"></script>
        <script type="text/javascript" src="js/jquery.history.js"></script>

        <script type="text/javascript" src="js/review.js"></script>
        <script type="text/javascript" src="js/tree.js"></script>

        <title>PHP CodeBrowser</title>
    </head>
    <body class="codebrowser">
        <div id="treeContainer">
            <div id="tree">
                <div id="treeHeader">
                    <a href="index.html" class='fileLink'>CodeBrowser</a>
                </div>
<?php echo $treeList; ?>
            </div>
            <div id="treeToggle" style="background-image: url('img/treeToggle-extended.png');"></div>
        </div>
        <div id="contentBox" style="display: inline-block; margin: 15px;">
            <div id="fileList">
                <table border="0" cellspacing="2" cellpadding="3">
<?php
$oddrow = true;
$preLen = strlen(PHPCodeBrowser\Helper\IOHelper::getCommonPathPrefix(array_keys($fileList)));

// Find out which types of errors have been found
$occuringErrorTypes = array (
    'CPD'        => false,
    'CRAP'       => false,
    'Checkstyle' => false,
    'Coverage'   => false,
    'PMD'        => false,
    'Padawan'    => false
);

foreach ($fileList as $file) {
    /** @var $file PHPCodeBrowser\File */
    foreach ($file->getIssues() as $issue) {
        $occuringErrorTypes[$issue->foundBy] = true;
    }
}

$occuringErrorTypes = array_keys(array_filter($occuringErrorTypes));

// Print the tables head
echo '                    <tr class="head">';
echo '                        <th><strong>File</strong></th>' . PHP_EOL;
echo '                        <th width="50px" align="center"><strong>Errors'
    . '</strong></th>' . PHP_EOL;
echo '                        <th width="50px" align="center"><strong>Warnings'
    . '</strong></th>' . PHP_EOL;

foreach ($occuringErrorTypes as $errorType) {
    echo "                        <th width='70px' align='center'><strong>"
        . "$errorType</strong></th>" . PHP_EOL;
}
echo '                    </tr>' . PHP_EOL;

// Print the file table
/** @var $f PHPCodeBrowser\File */
foreach ($fileList as $filename => $f) {
    $tag = $oddrow ? 'odd' : 'even';
    $oddrow = !$oddrow;
    $shortName = substr($filename, $preLen);
    $shortName = str_replace('\\', '/', $shortName);
    $errors = $f->getErrorCount();
    $warnings = $f->getWarningCount();

    $counts = array_fill_keys($occuringErrorTypes, 0);

    foreach ($f->getIssues() as $issue) {
        $counts[$issue->foundBy] += 1;
    }

    echo "                    <tr class='$tag'>" . PHP_EOL;
    echo "                        <td><a class='fileLink' "
        . "href='./$shortName.html'>$shortName</a></td>" . PHP_EOL;
    echo "                        <td align='center'><span class='errorCount'>"
        . "$errors</span></td>" . PHP_EOL;
    echo "                        <td align='center'>"
        . "<span class='warningCount'>$warnings</span></td>" . PHP_EOL;

    foreach ($counts as $count) {
        echo "                        <td align='center'>$count</td>" . PHP_EOL;
    }
    echo "                    </tr>" . PHP_EOL;
}
?>
                </table>
            </div>
        </div>
    </body>
</html>
