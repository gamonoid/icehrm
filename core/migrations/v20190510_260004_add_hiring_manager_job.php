<?php
namespace Classes\Migration;

use Candidates\Common\Model\Application;
use Candidates\Common\Model\Candidate;

class v20190510_260004_add_hiring_manager_job extends AbstractMigration{

    public function up(){
        $sql = <<<'SQL'
        Alter table Job add column `hiringManager` bigint(20) null;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Job add column `companyName` varchar(100) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Job add column `showHiringManager` enum('Yes','No') default NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        create table `HiringPipeline` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NULL,
            `type` enum('Short Listed','Phone Screen','Assessment','Interview','Offer','Hired','Rejected','Archived') default 'Short Listed',
            `notes` text DEFAULT NULL,
            primary key  (`id`)
        ) engine=innodb default charset=utf8;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        INSERT INTO HiringPipeline (`name`, `type`) VALUES
        ('Sourced', 'Short Listed'),
        ('Applied', 'Short Listed'),
        ('Phone Screen', 'Phone Screen'),
        ('Assessment', 'Assessment'),
        ('First Interview', 'Interview'),
        ('Second Interview', 'Interview'),
        ('Final Interview', 'Interview'),
        ('Offer Sent', 'Offer'),
        ('Offer Accepted', 'Offer'),
        ('Offer Rejected', 'Offer'),
        ('Not Qualified', 'Rejected'),
        ('Archived', 'Archived'),
        ('Hired', 'Hired');
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Candidates add column `hiringStage` bigint(20) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Candidates add column `jobId` bigint(20) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Candidates add column `source` enum('Sourced','Applied') default 'Sourced';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Candidates add column `emailSent` int(11) default 0;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Interviews add column `scheduleUpdated` int(11) default 0;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Interviews add column `interviewers` TEXT default NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        CREATE INDEX source_emailSent ON Candidates (source, emailSent);
SQL;
        $this->executeQuery($sql);

        return true;
    }
}
