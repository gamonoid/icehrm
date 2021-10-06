<?php
namespace Classes\Migration;

class v20211006_310001_loan_monthly_installment extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
create table `LoanMonthlyInstallments` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`year` int,
    `month` int,
    `amount` decimal(12,2) DEFAULT(0.00),
    `status` varchar(20),
    `notes` varchar(500),
    primary key(`id`)
) engine=innodb default charset=utf8;
SQL;
    $this->executeQuery($sql);
    }
        
    public function down(){
        return true;
    }
}


