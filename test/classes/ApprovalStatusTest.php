<?php

if(!class_exists("TestTemplate")) {
    include dirname(__FILE__).'/../TestTemplate.php';
}


class ApprovalStatusTest extends TestTemplate{

    var $travelRec = null;

    protected function setUp(){

        parent::setUp();
        $emp = new Employee();
        $emp->Load("id = ?",array(1));
        $emp->supervisor = 2;
        $emp->indirect_supervisors = json_encode(array(3,4));
        $emp->approver1 = 5;
        $emp->approver2 = 6;
        $emp->approver3 = 7;
        $emp->Save();



        $this->travelRec = new EmployeeTravelRecord();

        $this->travelRec->DB()->execute("delete from EmployeeTravelRecords");

        $this->travelRec->employee = 1;
        $this->travelRec->type = 'International';
        $this->travelRec->purpose = 'Testing';
        $this->travelRec->travel_from = 'Colombo';
        $this->travelRec->travel_to = 'Germany';
        $this->travelRec->travel_date = date("Y-m-d H:i:s");
        $this->travelRec->return_date = date("Y-m-d H:i:s");
        $this->travelRec->status = 'Pending';
        $this->travelRec->Save();
    }
    
    protected function tearDown(){
        parent::tearDown();
    }


    public function testInitializeApprovalChain(){
        $id = $this->travelRec->id;
        $this->initializeObjects();
        $as = ApprovalStatus::getInstance();
        $as->initializeApprovalChain('EmployeeTravelRecord',$id);
        $status = $as->getAllStatuses('EmployeeTravelRecord',$id);
        $this->assertEquals(3, count($status));
    }


    public function testUpdateApprovalStatus(){
        $id = $this->travelRec->id;
        $this->initializeObjects();
        $as = ApprovalStatus::getInstance();

        $as->initializeApprovalChain('EmployeeTravelRecord',$id);
        $resp = $as->updateApprovalStatus('EmployeeTravelRecord',$id,2,1);

        $this->assertNull($resp->getObject()[0]);
        $this->assertEquals(1, $resp->getObject()[1]->active);
        $this->assertEquals(1, $resp->getObject()[1]->level);


        $resp = $as->updateApprovalStatus('EmployeeTravelRecord',$id,3,1);
        $this->assertEquals(IceResponse::ERROR, $resp->getStatus());

        $resp = $as->updateApprovalStatus('EmployeeTravelRecord',$id,5,1);
        $this->assertEquals(0, $resp->getObject()[0]->active);
        $this->assertEquals(1, $resp->getObject()[0]->level);
        $this->assertEquals(1, $resp->getObject()[1]->active);
        $this->assertEquals(2, $resp->getObject()[1]->level);

        $resp = $as->updateApprovalStatus('EmployeeTravelRecord',$id,6,1);
        $this->assertEquals(0, $resp->getObject()[0]->active);
        $this->assertEquals(2, $resp->getObject()[0]->level);
        $this->assertEquals(1, $resp->getObject()[1]->active);
        $this->assertEquals(3, $resp->getObject()[1]->level);

        $resp = $as->updateApprovalStatus('EmployeeTravelRecord',$id,7,1);
        $this->assertEquals(1, $resp->getObject()[0]->active);
        $this->assertEquals(3, $resp->getObject()[0]->level);
        $this->assertNull($resp->getObject()[1]);


        fwrite(STDOUT, __METHOD__ . " End\n");
    }

}