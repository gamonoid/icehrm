<?php
namespace Classes\Migration;

/**
 * EmployeeSalary.amount was decimal(10,2), so the largest salary that could be saved
 * was 99,999,999.99. That is too small for currencies with large nominal values (for
 * example IDR), and with strict SQL mode the save failed with "Out of range value for
 * column 'amount'". Widen it to decimal(12,2), the size PayGrades.min_salary and
 * max_salary already use.
 */
class v20261123_100000_widen_employee_salary_amount extends AbstractMigration {

	public function up() {
		return $this->executeQuery(
			"ALTER TABLE EmployeeSalary MODIFY `amount` decimal(12,2) NOT NULL"
		);
	}

	public function down() {
		return true;
	}

}
