<?php

namespace Classes\Migration;

class v20260106_100001_create_leave_tables extends AbstractProMigration
{
    /**
     * Get the DDL for all leave-related tables
     */
    private function getLeaveDDL(): string
    {
        return <<<'SQL'
create table LeaveTypes
(
    id                                 bigint auto_increment
        primary key,
    name                               varchar(100)                     not null,
    supervisor_leave_assign            enum ('Yes', 'No') default 'Yes' null,
    employee_can_apply                 enum ('Yes', 'No') default 'Yes' null,
    apply_beyond_current               enum ('Yes', 'No') default 'Yes' null,
    leave_accrue                       enum ('No', 'Yes') default 'No'  null,
    carried_forward                    enum ('No', 'Yes') default 'No'  null,
    default_per_year                   decimal(10, 3)                   not null,
    carried_forward_percentage         int                default 0     null,
    carried_forward_leave_availability int                default 365   null,
    propotionate_on_joined_date        enum ('No', 'Yes') default 'No'  null,
    send_notification_emails           enum ('Yes', 'No') default 'Yes' null,
    leave_group                        bigint                           null,
    leave_color                        varchar(10)                      null,
    max_carried_forward_amount         int                default 0     null,
    employee_leave_period              enum ('Yes', 'No') default 'No'  null,
    leave_lock_period                  int                default 0     null,
    notice_period                      int                default 0     null,
    attachment_mandatory               enum ('Yes', 'No') default 'No'  null,
    notes                              text                             null,
    sandwich_leave                     tinyint            default 0     not null,
    constraint name
        unique (name)
);

create table LeavePeriods
(
    id         bigint auto_increment
        primary key,
    name       varchar(100)                                   not null,
    date_start date                                           null,
    date_end   date                                           null,
    status     enum ('Active', 'Inactive') default 'Inactive' null,
    country    bigint                                         null
);

create table HoliDays
(
    id          bigint auto_increment
        primary key,
    name        varchar(100)                                     not null,
    dateh       date                                             null,
    status      enum ('Full Day', 'Half Day') default 'Full Day' null,
    country     bigint                                           null,
    event_id    varchar(100)                  default ''         null,
    leave_group bigint                                           null,
    constraint holidays_dateh_country_group
        unique (dateh, country, leave_group)
);

create table WorkDays
(
    id      bigint auto_increment
        primary key,
    name    varchar(100)                                                        not null,
    status  enum ('Full Day', 'Half Day', 'Non-working Day') default 'Full Day' null,
    country bigint                                                              null,
    constraint workdays_name_country
        unique (name, country)
);

create table LeaveRules
(
    id                                 bigint auto_increment
        primary key,
    leave_type                         bigint                           not null,
    job_title                          bigint                           null,
    employment_status                  bigint                           null,
    employee                           bigint                           null,
    supervisor_leave_assign            enum ('Yes', 'No') default 'Yes' null,
    employee_can_apply                 enum ('Yes', 'No') default 'Yes' null,
    apply_beyond_current               enum ('Yes', 'No') default 'Yes' null,
    leave_accrue                       enum ('No', 'Yes') default 'No'  null,
    carried_forward                    enum ('No', 'Yes') default 'No'  null,
    default_per_year                   decimal(10, 3)                   not null,
    carried_forward_percentage         int                default 0     null,
    carried_forward_leave_availability int                default 365   null,
    propotionate_on_joined_date        enum ('No', 'Yes') default 'No'  null,
    leave_group                        bigint                           null,
    max_carried_forward_amount         int                default 0     null,
    exp_days                           int                              null,
    leave_period                       bigint                           null,
    department                         bigint                           null,
    employee_leave_period              enum ('Yes', 'No') default 'No'  null,
    constraint Fk_LeaveRules_department
        foreign key (department) references CompanyStructures (id),
    constraint Fk_LeaveRules_leave_period
        foreign key (leave_period) references LeavePeriods (id)
);

create table LeaveStartingBalance
(
    id           bigint auto_increment
        primary key,
    leave_type   bigint         not null,
    employee     bigint         null,
    leave_period bigint         not null,
    amount       decimal(10, 3) not null,
    note         text           null,
    created      datetime       null,
    updated      datetime       null
);

create table EmployeeLeaves
(
    id           bigint auto_increment
        primary key,
    employee     bigint                                                                                                          not null,
    leave_type   bigint                                                                                                          not null,
    leave_period bigint                                                                                                          not null,
    date_start   date                                                                                                            null,
    date_end     date                                                                                                            null,
    details      text                                                                                                            null,
    status       enum ('Approved', 'Pending', 'Rejected', 'Cancellation Requested', 'Cancelled', 'Processing') default 'Pending' null,
    attachment   varchar(100)                                                                                                    null,
    event_ids    text                                                                                                            null,
    constraint Fk_EmployeeLeaves_Employee
        foreign key (employee) references Employees (id)
            on update cascade on delete cascade,
    constraint Fk_EmployeeLeaves_LeavePeriods
        foreign key (leave_period) references LeavePeriods (id),
    constraint Fk_EmployeeLeaves_LeaveTypes
        foreign key (leave_type) references LeaveTypes (id)
);

create table EmployeeLeaveDays
(
    id             bigint auto_increment
        primary key,
    employee_leave bigint                                                                                                                                                                                            not null,
    leave_date     date                                                                                                                                                                                              null,
    leave_type     enum ('Full Day', 'Half Day - Morning', 'Half Day - Afternoon', '1 Hour - Morning', '2 Hours - Morning', '3 Hours - Morning', '1 Hour - Afternoon', '2 Hours - Afternoon', '3 Hours - Afternoon') not null,
    constraint Fk_EmployeeLeaveDays_EmployeeLeaves
        foreign key (employee_leave) references EmployeeLeaves (id)
            on update cascade on delete cascade
);

create table EmployeeLeaveLog
(
    id             bigint auto_increment
        primary key,
    employee_leave bigint                                                                                                          not null,
    user_id        bigint                                                                                                          null,
    data           varchar(500)                                                                                                    not null,
    status_from    enum ('Approved', 'Pending', 'Rejected', 'Cancellation Requested', 'Cancelled', 'Processing') default 'Pending' null,
    status_to      enum ('Approved', 'Pending', 'Rejected', 'Cancellation Requested', 'Cancelled', 'Processing') default 'Pending' null,
    created        datetime                                                                                                        null,
    constraint Fk_EmployeeLeaveLog_EmployeeLeaves
        foreign key (employee_leave) references EmployeeLeaves (id)
            on update cascade on delete cascade,
    constraint Fk_EmployeeLeaveLog_Users
        foreign key (user_id) references Users (id)
            on update cascade on delete set null
);
SQL;
    }

    public function up(): bool
    {
        $dbDelta = new DbDelta();
        $queries = $dbDelta->delta($this->getLeaveDDL(), true);

        $messages = $dbDelta->getMessages();
        $errors = $dbDelta->getErrors();

        if (!empty($messages)) {
            foreach ($messages as $message) {
                \Utils\LogManager::getInstance()->info("Leave tables migration: $message");
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                \Utils\LogManager::getInstance()->error("Leave tables migration error: $error");
            }
            $this->lastError = implode('; ', $errors);
            return false;
        }

        return true;
    }

    public function down(): bool
    {
        // We don't drop tables on down migration for safety
        return true;
    }
}
