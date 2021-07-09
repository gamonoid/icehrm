<?php
namespace Invoices\Pdf;

use Classes\Pdf\BasePdfTemplate;
use Invoices\Model\Invoice;

class InvoicePdf extends BasePdfTemplate
{

    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        parent::__construct();
        $this->invoice = $invoice;
    }

    public function process()
    {
        $this->addH3($this->form->name, 'B', 'L');
        $this->addBorderedText($this->invoice->description);
    }

//    protected function addFormItems()
//    {
//        $signatures = [];
//        $fields = json_decode($this->form->items);
//        foreach ($fields as $field) {
//            if ($field->field_type === 'signature') {
//                $signatures[$field->field_label] = $this->employeeForm->{$field->name};
//            } elseif ($field->field_type === 'fileupload') {
//                // ignore
//            } else {
//                $this->addKeyValue($field->field_label, $this->employeeForm->{$field->name}, $field->field_type);
//            }
//        }
//
//        foreach ($signatures as $name => $data) {
//            $this->addSignature($name, $data);
//        }
//    }
}
