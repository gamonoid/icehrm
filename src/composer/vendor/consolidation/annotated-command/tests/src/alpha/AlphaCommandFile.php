<?php
namespace Consolidation\TestUtils\alpha;

use Consolidation\AnnotatedCommand\CommandError;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\AssociativeList;
use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Input\InputOption;
use Consolidation\AnnotatedCommand\CommandData;

/**
 * Test file used in the testCommandDiscovery() test.
 *
 * This commandfile is found by the test.  The test search base is the
 * 'src' directory, and 'alpha' is one of the search directories available
 * for searching.
 */
class AlphaCommandFile
{
    /**
     * @command always:fail
     */
    public function alwaysFail()
    {
        return new CommandError('This command always fails.', 13);
    }

    /**
     * @command simulated:status
     */
    public function simulatedStatus()
    {
        return ['status-code' => 42];
    }

    /**
     * @command example:output
     */
    public function exampleOutput()
    {
        return 'Hello, World.';
    }

    /**
     * @command example:cat
     */
    public function exampleCat($one, $two = '', $options = ['flip' => false])
    {
        if ($options['flip']) {
            return "{$two}{$one}";
        }
        return "{$one}{$two}";
    }

    /**
     * @command example:echo
     */
    public function exampleEcho(array $args)
    {
        return ['item-list' => $args];
    }

    /**
     * @command example:message
     */
    public function exampleMessage()
    {
        return ['message' => 'Shipwrecked; send bananas.'];
    }

    /**
     * Test command with formatters
     *
     * @command example:table
     * @field-labels
     *   first: I
     *   second: II
     *   third: III
     * @usage example:table --format=yaml
     * @usage example:table --format=csv
     * @usage example:table --fields=first,third
     * @usage example:table --fields=III,II
     * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
     */
    public function exampleTable($options = ['format' => 'table', 'fields' => ''])
    {
        $outputData = [
            [ 'first' => 'One',  'second' => 'Two',  'third' => 'Three' ],
            [ 'first' => 'Eins', 'second' => 'Zwei', 'third' => 'Drei'  ],
            [ 'first' => 'Ichi', 'second' => 'Ni',   'third' => 'San'   ],
            [ 'first' => 'Uno',  'second' => 'Dos',  'third' => 'Tres'  ],
        ];
        return new RowsOfFields($outputData);
    }

    /**
     * Test word wrapping
     *
     * @command example:wrap
     * @field-labels
     *   first: First
     *   second: Second
     *
     * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
     */
    public function exampleWrap()
    {
        $data = [
            [
                'first' => 'This is a really long cell that contains a lot of data. When it is rendered, it should be wrapped across multiple lines.',
                'second' => 'This is the second column of the same table. It is also very long, and should be wrapped across multiple lines, just like the first column.',
            ]
        ];
        return new RowsOfFields($data);
    }

    /**
     * @hook option example:table
     */
    public function additionalOptionForExampleTable($command, $annotationData)
    {
        $command->addOption(
            'dynamic',
            '',
            InputOption::VALUE_NONE,
            'Option added by @hook option example:table'
        );
    }

    /**
     * Demonstrate an alter hook with an option
     *
     * @hook alter example:table
     * @option french Add a row with French numbers.
     * @usage example:table --french
     */
    public function alterFormatters($result, CommandData $commandData)
    {
        if ($commandData->input()->getOption('french')) {
            $result[] = [ 'first' => 'Un',  'second' => 'Deux',  'third' => 'Trois'  ];
        }

        return $result;
    }

    /**
     * Test command with formatters using an associative list
     *
     * @command example:list
     * @field-labels
     *   sftp_command: SFTP Command
     *   sftp_username: SFTP Username
     *   sftp_host: SFTP Host
     *   sftp_password: SFTP Password
     *   sftp_url: SFTP URL
     *   git_command: Git Command
     *   git_username: Git Username
     *   git_host: Git Host
     *   git_port: Git Port
     *   git_url: Git URL
     *   mysql_command: MySQL Command
     *   mysql_username: MySQL Username
     *   mysql_host: MySQL Host
     *   mysql_password: MySQL Password
     *   mysql_url: MySQL URL
     *   mysql_port: MySQL Port
     *   mysql_database: MySQL Database
     *   redis_command: Redis Command
     *   redis_port: Redis Port
     *   redis_url: Redis URL
     *   redis_password: Redis Password
     * @default-fields *_command
     * @return \Consolidation\OutputFormatters\StructuredData\AssociativeList
     */
    public function exampleAssociativeList()
    {
        $outputData = [
            'sftp_command' => 'sftp -o Port=2222 dev@appserver.dev.drush.in',
            'sftp_username' => 'dev',
            'sftp_host' => 'appserver.dev.drush.in',
            'sftp_password' => 'Use your account password',
            'sftp_url' => 'sftp://dev@appserver.dev.drush.in:2222',
            'git_command' => 'git clone ssh://codeserver.dev@codeserver.dev.drush.in:2222/~/repository.git wp-update',
            'git_username' => 'codeserver.dev',
            'git_host' => 'codeserver.dev.drush.in',
            'git_port' => 2222,
            'git_url' => 'ssh://codeserver.dev@codeserver.dev.drush.in:2222/~/repository.git',
            'mysql_command' => 'mysql -u pantheon -p4b33cb -h dbserver.dev.drush.in -P 16191 pantheon',
            'mysql_username' => 'pantheon',
            'mysql_host' => 'dbserver.dev.drush.in',
            'mysql_password' => '4b33cb',
            'mysql_url' => 'mysql://pantheon:4b33cb@dbserver.dev.drush.in:16191/pantheon',
            'mysql_port' => 16191,
            'mysql_database' => 'pantheon',
        ];
        return new AssociativeList($outputData);
    }

    /**
     * This command has no annotations; this means that it will not be
     * found when createCommandsFromClass() is called with
     * '$includeAllPublicMethods' set to false.
     */
    public function withoutAnnotations()
    {
        return 'ok';
    }

    /**
     * @command command:with-one-optional-argument
     *
     * This command has just one optional argument.
     *
     * Return a result only if not silent.
     *
     * @option silent Supress output.
     */
    public function commandWithOneOptionalArgument($who = 'world', $opts = ['silent|s' => false])
    {
        if (!$opts['silent']) {
            return "Hello, $who";
        }
    }

    /**
     * This should be a command, because it is annotated like one.
     *
     * @command get:serious
     */
    public function getSerious()
    {
        return 'very serious';
    }

    /**
     * This should not be a command, because it looks like an accessor and
     * has no @command annotation.
     */
    public function getLost()
    {
        return 'very lost';
    }
}
