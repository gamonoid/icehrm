<?php

namespace OnboardingAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\DbDelta;
use Classes\Migration\MigrationInterface;
use Utils\LogManager;

/**
 * Tables for the Onboarding extension.
 *
 * Workflows that HR builds live in OnboardingTemplates (the starter workflows ship as
 * JSON in admin/library and are never stored unless used). Each new hire gets an
 * Onboardings row and a snapshot of the workflow's steps in OnboardingSteps, so later
 * edits to a workflow never change onboardings already under way. `kind` is there so
 * offboarding can reuse the same engine.
 *
 * DbDelta rules: column defaults carry no commas and every index is declared inline.
 * A failed CREATE never reaches getErrors(), so up() checks the tables exist.
 */
class CreateTables extends AbstractMigration implements MigrationInterface
{
    const TABLES = array(
        'OnboardingTemplates',
        'Onboardings',
        'OnboardingSteps',
        'OnboardingActivity',
    );

    public function getName()
    {
        return 'onboarding_create_tables';
    }

    private function getDDL()
    {
        return <<<'SQL'
create table OnboardingTemplates
(
    id                bigint auto_increment primary key,
    kind              varchar(20)  default 'onboarding' not null,
    name              varchar(150) not null,
    description       text         null,
    welcome_message   text         null,
    welcome_video_url varchar(500) null,
    first_day_info    text         null,
    steps             longtext     null,
    status            varchar(20)  default 'Active' not null,
    library_key       varchar(80)  null,
    created_by        bigint       null,
    created           datetime     null,
    updated           datetime     null,
    key Ix_OnboardingTemplates_Status (status, name),
    key Ix_OnboardingTemplates_Library (library_key)
);

create table Onboardings
(
    id                bigint auto_increment primary key,
    kind              varchar(20)  default 'onboarding' not null,
    template          bigint       null,
    template_name     varchar(150) null,
    invitation        bigint       null,
    employee          bigint       null,
    first_name        varchar(100) not null,
    last_name         varchar(100) not null,
    email             varchar(100) not null,
    start_date        date         not null,
    job_title         bigint       null,
    department        bigint       null,
    supervisor        bigint       null,
    buddy             bigint       null,
    employment_status bigint       null,
    country           char(2)      null,
    user_level        varchar(20)  default 'Employee' not null,
    welcome_message   text         null,
    welcome_video_url varchar(500) null,
    first_day_info    text         null,
    status            varchar(20)  default 'Invited' not null,
    accepted_at       datetime     null,
    activated_at      datetime     null,
    completed_at      datetime     null,
    created_by        bigint       null,
    created           datetime     null,
    updated           datetime     null,
    key Ix_Onboardings_Status (status, start_date),
    key Ix_Onboardings_Employee (employee),
    key Ix_Onboardings_Invitation (invitation),
    constraint Fk_Onboardings_Employee foreign key (employee) references Employees (id) on delete cascade on update cascade
);

create table OnboardingSteps
(
    id               bigint auto_increment primary key,
    onboarding       bigint       not null,
    sort_order       int          default 0 not null,
    phase            varchar(20)  default 'before' not null,
    type             varchar(20)  not null,
    title            varchar(200) not null,
    instructions     text         null,
    owner_type       varchar(20)  default 'new_hire' not null,
    owner_employee   bigint       null,
    due_offset_days  int          default 0 not null,
    due_date         date         null,
    required         tinyint      default 1 not null,
    needs_review     tinyint      default 0 not null,
    config           longtext     null,
    status           varchar(20)  default 'Pending' not null,
    response         longtext     null,
    link_ref         bigint       null,
    completed_at     datetime     null,
    completed_by     bigint       null,
    review_note      text         null,
    last_reminder_at datetime     null,
    created          datetime     null,
    updated          datetime     null,
    key Ix_OnboardingSteps_Onboarding (onboarding, sort_order),
    key Ix_OnboardingSteps_Owner (owner_employee, status),
    key Ix_OnboardingSteps_Status (status, due_date),
    constraint Fk_OnboardingSteps_Onboarding foreign key (onboarding) references Onboardings (id) on delete cascade on update cascade
);

create table OnboardingActivity
(
    id         bigint auto_increment primary key,
    onboarding bigint      not null,
    step       bigint      null,
    actor      bigint      null,
    action     varchar(40) not null,
    details    text        null,
    created    datetime    null,
    key Ix_OnboardingActivity_Onboarding (onboarding, created),
    constraint Fk_OnboardingActivity_Onboarding foreign key (onboarding) references Onboardings (id) on delete cascade on update cascade
);
SQL;
    }

    public function up()
    {
        $dbDelta = new DbDelta();
        $dbDelta->delta($this->getDDL(), true);

        $errors = $dbDelta->getErrors();
        if (!empty($errors)) {
            LogManager::getInstance()->error('Onboarding migration errors: ' . implode('; ', $errors));
            return false;
        }

        foreach (self::TABLES as $table) {
            // Not "SHOW TABLES LIKE ?": SHOW does not take a placeholder here.
            $rows = $this->db()->Execute(
                'SELECT COUNT(*) AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                array($table)
            );
            if (!is_array($rows) || empty($rows[0]['c'])) {
                LogManager::getInstance()->error('Onboarding migration: table ' . $table . ' was not created');
                return false;
            }
        }
        return true;
    }

    public function down()
    {
        return true;
    }
}
