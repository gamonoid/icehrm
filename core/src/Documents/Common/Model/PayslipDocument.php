<?php


namespace Documents\Common\Model;

class PayslipDocument extends EmployeeDocument
{
    public function getFinder()
    {
        return new class extends EmployeeDocumentFinderProxy {
            protected function getAdditionalQuery()
            {
                return 'hidden = 1 ';
            }
        };
    }
}
