<?php
namespace Invoices;

use Classes\Migration\AbstractMigration;

class Migration extends AbstractMigration
{
    public function up()
    {
        $sql = <<<'SQL'
create table Invoices
(
	id bigint auto_increment primary key,
	paymentId bigint not null,
	invoiceId bigint not null,
	description varchar(500) charset utf8 not null,
	buyerName varchar(200) charset utf8 not null,
	buyerAddress varchar(200) charset utf8 not null,
	buyerPostalCode varchar(200) charset utf8 not null,
	buyerCountry varchar(200) charset utf8 not null,
	buyerVatId varchar(50) charset utf8 not null,
	sellerName varchar(200) charset utf8 not null,
	sellerAddress varchar(200) null,
	sellerCountry varchar(200) charset utf8 not null,
	sellerVatId varchar(50) charset utf8 not null,
	amount decimal(10,2) default 0.00 null,
	vat decimal(10,2) default 0.00 null,
	vatRate decimal(10,2) default 0.00 null,
	issuedDate datetime null,
	dueDate datetime null,
	paidDate datetime null,
	created datetime null,
	updated datetime null,
	status enum('Pending', 'Paid', 'Processing', 'Draft', 'Sent', 'Canceled') collate utf8_unicode_ci default 'Pending' null,
	acceptPayments tinyint default 0 null,
	buyerEmail varchar(125) charset utf8 null,
	items text charset utf8 null,
	constraint invoiceId
		unique (invoiceId)
)
collate=utf8mb4_unicode_ci;
SQL;
        return $this->executeQuery($sql);
    }

    public function down()
    {
        $sql = <<<'SQL'
DROP TABLE IF EXISTS `Invoices`; 
SQL;
        return $this->executeQuery($sql);
    }
}