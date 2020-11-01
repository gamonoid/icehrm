<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>
            Mayflower Code Browser - Source Code
        </title>
        <link rel="stylesheet" type="text/css" href="<?php print $csspath; ?>js/jquery.sidebar/css/codebrowser/sidebar.css" />
        <link rel="stylesheet" type="text/css" href="<?php print $csspath; ?>css/global.css" />
        <link rel="stylesheet" type="text/css" href="<?php print $csspath; ?>css/cruisecontrol.css" />
        <link rel="stylesheet" type="text/css" href="<?php print $csspath; ?>css/review.css" />

        <script type="text/javascript" src="<?php print $csspath; ?>js/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="<?php print $csspath; ?>js/jquery.sidebar/jquery-ui-1.7.2.custom.min.js"></script>
        <script type="text/javascript" src="<?php print $csspath; ?>js/jquery.sidebar/jquery.sidebar.js"></script>
        <script type="text/javascript" src="<?php print $csspath; ?>js/jquery.cluetip/lib/jquery.hoverIntent.js"></script>
        <script type="text/javascript" src="<?php print $csspath; ?>js/jquery.cluetip/lib/jquery.bgiframe.min.js"></script>
        <script type="text/javascript" src="<?php print $csspath; ?>js/jquery.cluetip/jquery.cluetip.min.js"></script>

        <script type="text/javascript" src="<?php print $csspath; ?>js/review.js"></script>
    </head>
    <body class="codebrowser">
        <div class="header">
            <a href="./<?php echo $csspath; ?>index.html">Go back to index</a> |
            <a href="https://github.com/Mayflower/PHP_CodeBrowser">PHP CodeBrowser</a>
        </div>
        <hr/>
        <div id="review">
            <div class="filepath">
                <?php echo str_replace('\\', '/', $filepath); ?>
            </div>

            <?php echo $source; ?>
            <?php if (!empty($issues)) : ?>

            <div id="sidebar">
                <table cellpadding="3">
                    <thead>
                        <tr>
                            <th width="40px" align="center">
                                <strong>start</strong>
                            </th>
                            <th width="40px" align="center">
                                <strong>end</strong>
                            </th>
                            <th>
                                <strong>comment</strong>
                            </th>
                            <th width="120px">
                                <strong>type of error</strong>
                            </th>
                            <th width="60px">
                                <strong>severity</strong>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($issues as $issue):?>

                        <tr class="<?php print $issue->foundBy; ?>">
                            <td align="center">
                                <a href="#line_<?php print $issue->lineStart;?>" onclick="switchLine('line_<?php print $issue->lineStart;?>')">
                                <?php print $issue->lineStart; ?></a>
                            </td>
                            <td align="center">
                                <a href="#line_<?php print $issue->lineEnd;?>" onclick="switchLine('line_<?php print $issue->lineStart;?>'); new Effect.Highlight('line_<?php print $issue->lineStart."-".$issue->lineEnd; ?>', {duration: 1.5}); return false">
                                <?php print $issue->lineEnd;?></a>
                            </td>
                            <td>
                                <a href="#line_<?php print $issue->lineStart; ?>" onclick="switchLine('line_<?php print $issue->lineStart;?>'); new Effect.Highlight('line_<?php print $issue->lineStart."-".$issue->lineEnd; ?>', {duration: 1.5}); return false">
                                <?php print (string)$issue->description;?></a>
                            </td>
                            <td>
                                <?php print $issue->foundBy;?>
                            </td>
                            <td>
                                <?php print $issue->severity;?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <script type="text/javascript" language="javascript">
                $(initReview);
            </script>
            <?php endif; ?>
        </div>
    </body>
</html>
