<?php
namespace Company\Admin\Api;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;

class CompanyApiController extends IceApiController
{
    public function registerEndPoints()
    {
        // Get company structure details with children and employees
        self::register(
            REST_API_PATH . 'company/structures/(:num)/details',
            self::GET,
            function ($id) {
                $this->getCompanyStructureDetails($id);
            }
        );
    }

    private function getCompanyStructureDetails($id)
    {
        $restEndpoint = new RestEndPoint();

        // Load the company structure
        $structure = new CompanyStructure();
        $structure->Load("id = ?", [$id]);

        if (empty($structure->id)) {
            $restEndpoint->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'Company structure not found'
            ));
            return;
        }

        // Get child structures
        $childStructure = new CompanyStructure();
        $children = $childStructure->Find("parent = ?", [$id]);
        $childrenData = [];
        foreach ($children as $child) {
            $childrenData[] = [
                'id' => $child->id,
                'title' => $child->title,
                'type' => $child->type,
                'timezone' => $child->timezone,
            ];
        }

        // Get employees attached to this structure
        $employee = new Employee();
        $employees = $employee->Find("department = ? AND status = ?", [$id, 'Active']);
        $employeesData = [];

        foreach ($employees as $emp) {
			$emp = FileService::getInstance()->updateSmallProfileImage($emp);

            $employeesData[] = [
                'id' => $emp->id,
                'employee_id' => $emp->employee_id,
                'first_name' => $emp->first_name,
                'last_name' => $emp->last_name,
                'job_title' => $emp->job_title,
                'work_email' => $emp->work_email,
                'image' => $emp->image,
            ];
        }

        // Get leads/heads
        $headsData = [];
        if (!empty($structure->heads)) {
            $heads = json_decode($structure->heads, true);
            if (is_array($heads)) {
                foreach ($heads as $headId) {
                    $head = new Employee();
                    $head->Load("id = ?", [$headId]);
                    if (!empty($head->id)) {
                        $imageUrl = null;
                        if (!empty($head->image)) {
                            try {
                                $imageUrl = FileService::getInstance()->getFileUrl($head->image);
                            } catch (\Exception $e) {
                                $imageUrl = FileService::getInstance()->generateProfileImage(
                                    $head->first_name,
                                    $head->last_name
                                );
                            }
                        } else {
                            $imageUrl = FileService::getInstance()->generateProfileImage(
                                $head->first_name,
                                $head->last_name
                            );
                        }

                        $headsData[] = [
                            'id' => $head->id,
                            'employee_id' => $head->employee_id,
                            'first_name' => $head->first_name,
                            'last_name' => $head->last_name,
                            'job_title' => $head->job_title,
                            'work_email' => $head->work_email,
                            'image' => $imageUrl,
                        ];
                    }
                }
            }
        }

        $result = [
            'id' => $structure->id,
            'title' => $structure->title,
            'description' => $structure->description,
            'address' => $structure->address,
            'type' => $structure->type,
            'country' => $structure->country,
            'timezone' => $structure->timezone,
            'parent' => $structure->parent,
            'heads' => $headsData,
            'children' => $childrenData,
            'employees' => $employeesData,
            'employeeCount' => count($employeesData),
            'childCount' => count($childrenData),
        ];

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $result));
    }
}
