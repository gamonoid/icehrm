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

    /**
     * Payslips are Admin-only, plus the owning employee through the only-me path.
     *
     * PayslipDocument inherits EmployeeDocument, whose matrices are written for
     * general HR documents: Manager gets ("get","element") — which would let any
     * manager read their reports' PAYSLIPS — and Employee gets an all-rows "get".
     * Payslips are salary data, so both are closed here.
     *
     * The owner keeps "get" and "element": PayslipDocument is a registered user
     * table (DocumentsModulesManager::addUserClass), so the only-me "get" is
     * row-filtered to the caller's own rows by BaseService::get(), which is what
     * renders the employee's "My Payslips" list. Write verbs are deliberately not
     * granted — payslips are produced by payroll, never edited by their subject.
     */
    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array("get", "element");
    }

}
