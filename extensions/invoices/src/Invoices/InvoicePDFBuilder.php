<?php
namespace Invoices;

use Classes\Authorizable;
use Classes\BaseService;
use Classes\IceResponse;
use Classes\Pdf\PdfBuilder;
use Invoices\Model\Invoice;
use Invoices\Pdf\InvoicePdf;


class InvoicePDFBuilder implements Authorizable, PdfBuilder
{

    protected $formId;

    public function __construct($formId)
    {
        $this->formId = $formId;
    }

    public function granted(): bool
    {
        $empForm = new EmployeeForm();
        $empForm->Load("id = ?", array($this->formId));

        $currentEmployeeId = BaseService::getInstance()->getCurrentProfileId();
        $user = BaseService::getInstance()->getCurrentUser();
        return ($currentEmployeeId == $empForm->employee
            || $user->user_level === 'Admin'
            || BaseService::getInstance()->isSubordinateEmployee($currentEmployeeId, $empForm->employee)
        );
    }

    public function createPdf()
    {
        $response = FormsActionManager::getFormDataById($this->formId);
        if ($response->getStatus() === IceResponse::ERROR) {
            return null;
        }

        $form = $response->getData()['form'];
        $invoiceForm = $response->getData()['data'];

        $invoice = new Invoice();
        $invoice->Load('id = ?', [$invoiceForm->invoice]);
        //$employee = FileService::getInstance()->updateSmallProfileImage($employee);

        $pdf = new InvoicePdf($invoice);
        $pdf->initialize($form->name);
        $pdf->process();

        return $pdf;
    }
}
