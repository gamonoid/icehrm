<?php

namespace Company_assetsAdmin\Migrations;

use Classes\BaseService;
use Classes\Migration\AbstractMigration;
use Classes\Migration\DbDelta;
use Classes\Migration\MigrationInterface;

class CreateTables extends AbstractMigration implements MigrationInterface
{
    public function getName()
    {
        return 'company_assets_create_tables';
    }

    private function getDDL()
    {
        return <<<'SQL'
create table AssetTypes
(
    id          bigint auto_increment primary key,
    name        varchar(100) not null,
    description text         null,
    attachment  varchar(100) null,
    custom_fields text       null,
    created     datetime     null,
    updated     datetime     null,
    constraint name unique (name)
);

create table CompanyAssets
(
    id             bigint auto_increment primary key,
    code           varchar(50)                                                     not null,
    name           varchar(150)                                                    null,
    type           bigint                                                          null,
    serial_number  varchar(100)                                                    null,
    status         enum('Available','Assigned','In Repair','Retired') default 'Available' null,
    employee       bigint                                                          null,
    department     bigint                                                          null,
    purchase_date  date                                                            null,
    purchase_price decimal(15,2)                                                   null,
    warranty_end   date                                                            null,
    location       varchar(200)                                                    null,
    notes          text                                                            null,
    attachment     varchar(100)                                                    null,
    description    text                                                            null,
    custom_field_values text                                                       null,
    created        datetime                                                        null,
    updated        datetime                                                        null,
    constraint code unique (code),
    constraint Fk_CompanyAssets_AssetTypes
        foreign key (type) references AssetTypes (id)
            on update cascade on delete set null,
    constraint Fk_CompanyAssets_CompanyStructures
        foreign key (department) references CompanyStructures (id)
            on update cascade on delete set null,
    constraint Fk_CompanyAssets_Employees
        foreign key (employee) references Employees (id)
            on update cascade on delete set null
);
SQL;
    }

    public function up()
    {
        $dbDelta = new DbDelta();
        $dbDelta->delta($this->getDDL(), true);

        $errors = $dbDelta->getErrors();
        if (!empty($errors)) {
            \Utils\LogManager::getInstance()->error("Company Assets migration errors: " . implode('; ', $errors));
            return false;
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
