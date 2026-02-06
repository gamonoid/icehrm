<?php
namespace Classes\Cron\Task;

use Classes\Cron\IceTask;
use Payroll\Admin\Api\PayrollActionManager;
use Payroll\Common\Model\Payroll;
use Utils\LogManager;

class PayrollProcessTask implements IceTask
{
    public function execute($cron)
    {
        $payroll = new Payroll();
        $payrolls = $payroll->Find(
            'status = ?',
            [
                PayrollActionManager::PAYROLL_STATUS_COMPLETING,
            ]
        );

        if (empty($payrolls)) {
            return;
        }
        $payroll = $payrolls[0];
		$this->complete($payroll);
    }

    /**
     * @param $payroll
     */
    public function process($payroll)
    {
        $req = new \stdClass();
        $req->rowTable = 'PayrollEmployee';
        $req->columnTable = 'PayrollColumn';
        $req->valueTable = 'PayrollData';
        $req->payrollId = $payroll->id;
        $req->save = 1;

        $payrollActionManager = new PayrollActionManager();
        $payrollActionManager->getAllData($req, true);

        $payroll->status = PayrollActionManager::PAYROLL_STATUS_PROCESSED;
        $payroll->Save();
    }

    public function complete($payroll)
    {
        $payrollActionManager = new PayrollActionManager();
        $payrollActionManager->generatePayslips($payroll->id);

        $payroll->status = PayrollActionManager::PAYROLL_STATUS_COMPLETED;
        $payroll->Save();
    }
}
