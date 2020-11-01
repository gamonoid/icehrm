# PHP_CodeBrowser #

[![Latest Stable Version](https://poser.pugx.org/mayflower/php-codebrowser/v/stable.png)](https://packagist.org/packages/mayflower/php-codebrowser)
[![Build Status](https://travis-ci.org/mayflower/PHP_CodeBrowser.png?branch=master)](https://travis-ci.org/mayflower/PHP_CodeBrowser)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Mayflower/PHP_CodeBrowser/badges/quality-score.png?s=2c0379f0efea966daeaef3fc5abf8adb4a910b24)](https://scrutinizer-ci.com/g/Mayflower/PHP_CodeBrowser/)
[![Code Coverage](https://scrutinizer-ci.com/g/Mayflower/PHP_CodeBrowser/badges/coverage.png?s=543238e3d9fb4584d8cb31e3af48e67ed846f9e5)](https://scrutinizer-ci.com/g/Mayflower/PHP_CodeBrowser/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/79205008-1c3d-4142-ab81-a9465008d440/mini.png)](https://insight.sensiolabs.com/projects/79205008-1c3d-4142-ab81-a9465008d440)

## Structure ##

    |--> bin/           PHP_CodeBrowser scripts
    |--> src/           Source files for PHP_CodeBrowser
    |   |--> Plugins/   Plugins for different error handling/types
    |
    |--> templates/     Template files for PHP_CodeBrowser
    |   |--> css/       Used CSS by templates, Color definition for errors
    |   |--> img/       Used images for PHP_CodeBrowser
    |   |--> js/        Used javascript for PHP_CodeBrowser
    |
    |--> tests/         PHPUnit test suite
    |
    |--> package.xml    PEAR package information file
    |
    |--> LICENCE        Licence information
    |--> README         Structure and install information
    |--> CHANGELOG      Update information

## Installation ##

### Git Checkout ###

    $ git clone git://github.com/Mayflower/PHP_CodeBrowser.git

### Installation via Composer ###

Add this line to the require section in composer.json:

    "mayflower/php-codebrowser": "~1.1"

Or to install it globally

    composer global require "mayflower/php-codebrowser=~1.1"
    
### Get PHAR ###
    
see [Releases](https://github.com/Mayflower/PHP_CodeBrowser/releases)

## Usage ##

### Shell Usage ###

    Try ./bin/phpcb.php -h for usage information.

### Integration in Jenkins, CruiseControl and Hudson ###

    ...
    <!-- phpcb should be called after xml file generation -->
    <target name="build" depends="...,phpcb" />
    ...
    <target name="phpcb">
        <exec executable="phpcb">
            <arg line="--log path/to/log/dir
                       --output path/to/output/dir/
                       --source path/to/source/dir/" />
        </exec>
    </target>
    ...

## View the Results ##

### Webbrowser ###

Open `/path/to/defined/output/index.html`.

### CruiseControl ###

#### config.xml ####

    <publishers>
      <artifactspublisher dir="path/to/output" dest="artifacts/${project.name}" subdirectory="PhpCbIdentifier" />
      ...
    </publishers>

#### main.jsp ####

    <cruisecontrol:tab name="PHP_CodeBrowser" label="PHP_CodeBrowser">
      <cruisecontrol:artifactsLink>
         <iframe src="<%=request.getContextPath() %>/<%= artifacts_url %>/PhpCbIdentifier/index.html" class="tab-content">
         </iframe>
      </cruisecontrol:artifactsLink>
    </cruisecontrol:tab>

### Jenkins/Hudson ###

Have a look at the [standard template for Jenkins jobs for PHP projects](https://github.com/sebastianbergmann/php-jenkins-template) to see how PHP_CodeBrowser can be used together with Jenkins.

## Contact Information ##

If you have any questions you may get in contact with: Elger Thiele <elger DOT thiele AT mayflower DOT de> or Thorsten Rinne <thorsten DOT rinne AT mayflower DOT de>
