<?php
namespace Documents\Admin\Api;

use Classes\AbstractModuleManager;
use Classes\Macaw;
use Classes\SystemTasks\SystemTasksService;
use Documents\Rest\EmployeeDocumentsRestApi;

class DocumentsAdminManager extends AbstractModuleManager
{

    public function initialize()
    {
        SystemTasksService::getInstance()->registerTaskCreator((new DocumentTaskCreator()));
    }

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
        $this->addFileFieldMapping('EmployeeDocument', 'attachment', 'name');
    }

    public function initializeDatabaseErrorMappings()
    {
        $this->addDatabaseErrorMapping(
            'CONSTRAINT `Fk_EmployeeDocuments_Documents` FOREIGN KEY',
            'Can not delete Document Type, users have already uploaded these types of documents'
        );
    }

    public function setupRestEndPoints()
    {
        Macaw::get(
            REST_API_PATH.'employees/documents',
            function () {
                $empRestEndPoint = new EmployeeDocumentsRestApi();
                $empRestEndPoint->process('listAll');
            }
        );

        Macaw::get(
            REST_API_PATH.'employees/(:num)/documents',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeDocumentsRestApi();
                $empRestEndPoint->process('listAll', $pathParams);
            }
        );

        Macaw::get(
            REST_API_PATH.'employees/documents/(:num)',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeDocumentsRestApi();
                $empRestEndPoint->process('get', $pathParams);
            }
        );

        Macaw::get(
            REST_API_PATH.'employees/documents/(:num)/file',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeDocumentsRestApi();
                $empRestEndPoint->process('getDocumentFile', $pathParams);
            }
        );
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('Document');
        $this->addModelClass('CompanyDocument');
        $this->addModelClass('EmployeeDocument');
        $this->addModelClass('PayslipDocument');
        $this->addModelClass('EmployeeDocumentNotification');
    }
}
