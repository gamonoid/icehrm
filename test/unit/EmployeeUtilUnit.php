<?php

namespace Test\Unit;

use Employees\Admin\Api\EmployeeUtil;

class EmployeeUtilUnit extends \TestTemplate
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testMapping()
    {
        $emplyeeUtils = new EmployeeUtil();
        $data = json_decode($emplyeeUtils->getMapping(), true);

        $this->assertEquals($data['nationality'][0], 'Nationality');
    }
}
