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
            'status = ? or status = ?',
            [
                PayrollActionManager::PAYROLL_STATUS_PROCESSING,
                PayrollActionManager::PAYROLL_STATUS_COMPLETING,
            ]
        );
        if (empty($payrolls)) {
            return;
        }
        $payroll = $payrolls[0];

        if ($payroll->status === PayrollActionManager::PAYROLL_STATUS_PROCESSING) {
            $this->process($payroll);
        } elseif ($payroll->status === PayrollActionManager::PAYROLL_STATUS_COMPLETING) {
            $this->complete($payroll);
        }
    }

    /**
     * @param $payroll
     */
    private function process($payroll)
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

    private function complete($payroll)
    {
        $payrollActionManager = new PayrollActionManager();
        $payrollActionManager->generatePayslips($payroll->id);

        $payroll->status = PayrollActionManager::PAYROLL_STATUS_COMPLETED;
        $payroll->Save();
    }
}
