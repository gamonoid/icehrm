<?php
namespace Test\Integration;

use Classes\Approval\ApprovalStatus;
use Employees\Common\Model\Employee;
use Test\Helper\EmployeeTestDataHelper;
use Travel\Common\Model\EmployeeTravelRecord;

class ApprovalStatusIntegration extends \TestTemplate
{

    protected $travelRec = null;
    protected $ids = null;

    protected function setUp()
    {

        parent::setUp();
        $ids = [];
        for ($i = 1; $i <= 7; $i++) {
            $ids[] = EmployeeTestDataHelper::insertRandomEmployee();
        }

        $emp = new Employee();
        $emp->Load("id = ?", array($ids[0]));
        $emp->supervisor = $ids[1];
        $emp->indirect_supervisors = json_encode(array($ids[2],$ids[3]));
        $emp->approver1 = $ids[4];
        $emp->approver2 = $ids[5];
        $emp->approver3 = $ids[6];
        $emp->Save();



        $this->travelRec = new EmployeeTravelRecord();

        $this->travelRec->employee = $ids[0];
        $this->travelRec->type = 'International';
        $this->travelRec->purpose = 'Testing';
        $this->travelRec->travel_from = 'Colombo';
        $this->travelRec->travel_to = 'Germany';
        $this->travelRec->travel_date = date("Y-m-d H:i:s");
        $this->travelRec->return_date = date("Y-m-d H:i:s");
        $this->travelRec->status = 'Pending';
        $this->travelRec->Save();

        $this->ids = $ids;
    }

    protected function tearDown()
    {
        parent::tearDown();
        foreach ($this->ids as $id) {
            $employee = new Employee();
            $employee->Load("id = ?", array($id));
            $employee->Delete();
        }

        $this->travelRec->DB()->execute("delete from EmployeeTravelRecords");
        $this->travelRec->DB()->execute("delete from EmployeeApprovals");
    }


    public function testInitializeApprovalChain()
    {
        $id = $this->travelRec->id;
        $as = ApprovalStatus::getInstance();
        $as->initializeApprovalChain('EmployeeTravelRecord', $id);
        $status = $as->getAllStatuses('EmployeeTravelRecord', $id);
        $this->assertEquals(3, count($status));
        $this->assertEquals($this->ids[4], $status[0]->approver);
        $this->assertEquals($this->ids[5], $status[1]->approver);
        $this->assertEquals($this->ids[6], $status[2]->approver);
    }


    public function testUpdateApprovalStatus()
    {
        $id = $this->travelRec->id;
        $as = ApprovalStatus::getInstance();

        $as->initializeApprovalChain('EmployeeTravelRecord', $id);

        // Supervisor should approve first
        $resp = $as->updateApprovalStatus('EmployeeTravelRecord', $id, $this->ids[1], 1);
        $this->assertNull($resp->getObject()[0]);
        $this->assertEquals(1, $resp->getObject()[1]->active);
        $this->assertEquals(1, $resp->getObject()[1]->level);

        // Now if a an indirect supervisor try to approve it should fail
        $resp = $as->updateApprovalStatus('EmployeeTravelRecord', $id, $this->ids[2], 1);
        $this->assertEquals(\Classes\IceResponse::ERROR, $resp->getStatus());

        // First approver approves
        $resp = $as->updateApprovalStatus('EmployeeTravelRecord', $id, $this->ids[4], 1);
        $this->assertEquals(0, $resp->getObject()[0]->active);
        $this->assertEquals(1, $resp->getObject()[0]->level);
        $this->assertEquals(1, $resp->getObject()[1]->active);
        $this->assertEquals(2, $resp->getObject()[1]->level);

        // Second approver approves
        $resp = $as->updateApprovalStatus('EmployeeTravelRecord', $id, $this->ids[5], 1);
        $this->assertEquals(0, $resp->getObject()[0]->active);
        $this->assertEquals(2, $resp->getObject()[0]->level);
        $this->assertEquals(1, $resp->getObject()[1]->active);
        $this->assertEquals(3, $resp->getObject()[1]->level);

        // Third approver approves
        $resp = $as->updateApprovalStatus('EmployeeTravelRecord', $id, $this->ids[6], 1);
        $this->assertEquals(1, $resp->getObject()[0]->active);
        $this->assertEquals(3, $resp->getObject()[0]->level);
        $this->assertNull($resp->getObject()[1]);
    }
}
