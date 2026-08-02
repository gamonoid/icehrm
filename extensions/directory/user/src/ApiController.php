<?php
namespace Directory\User;

use Classes\BaseService;
use Classes\Data\Query\DataQuery;
use Classes\IceApiController;
use Classes\IceResponse;
use Directory\Common\Model\StaffDirectory;
use Model\BaseModel;

class ApiController extends IceApiController
{

	public function registerEndPoints() {
        // REST Api get request
        self::register(
            REST_API_PATH.'staff-directory',
            self::POST,
            function () {
                $body = $this->getRequestBody();
                $query = new DataQuery('StaffDirectory');
                $page = 1;
                if (isset($body['page']) && intval($body['page']) > 0) {
                    $page = intval($body['page']);
                }
                $limit = static::DEFAULT_LIMIT;
                if (isset($body['limit']) && intval($body['limit']) > 0) {
                    $limit = intval($body['limit']);
                }
                $query->setLength($limit);

                $query->setSearchColumns([
                    'first_name',
                    'middle_name',
                    'last_name',
                ]);

                $query->setFilters(['status'=>'Active']);

                $mapping = <<<JSON
{
  "nationality": [ "Nationality", "id", "name" ],
  "ethnicity": [ "Ethnicity", "id", "name" ],
  "immigration_status": [ "ImmigrationStatus", "id", "name" ],
  "employment_status": [ "EmploymentStatus", "id", "name" ],
  "job_title": [ "JobTitle", "id", "name" ],
  "pay_grade": [ "PayGrade", "id", "name" ],
  "country": [ "Country", "code", "name" ],
  "province": [ "Province", "id", "name" ],
  "department": [ "CompanyStructure", "id", "title" ],
  "supervisor": [ "Employee", "id", "first_name+last_name" ],
  "indirect_supervisors": [ "Employee", "id", "first_name+last_name" ],
  "approver1": [ "Employee", "id", "first_name+last_name" ],
  "approver2": [ "Employee", "id", "first_name+last_name" ],
  "approver3": [ "Employee", "id", "first_name+last_name" ]
}
JSON;


                $query->setFieldMapping($mapping);

                if (!empty($body['search'])) {
                    $query->setSearchTerm($body['search']);
                }

                //$query->setOrderBy('first_name');

                $result = $this->listByQuery($query, $page, $limit);
                if ($result->getStatus() !== IceResponse::SUCCESS) {
                    $this->sendResponse($result);
                }

                $resultData = $result->getData();
                $resultData['total'] = $this->getCount($query);
                $this->sendResponse(new IceResponse(IceResponse::SUCCESS, $resultData));
            }
        );

        self::register(
            REST_API_PATH.'staff-random',
            self::POST,
            function () {
                $body = $this->getRequestBody();
                $limit = static::DEFAULT_LIMIT;
                if (isset($body['limit']) && intval($body['limit']) > 0) {
                    $limit = intval($body['limit']);
                }

                $staff = new StaffDirectory();
                $list = $staff->Find("status = ? ORDER BY RAND() LIMIT ?", array('Active', $limit));

                $this->sendResponse(new IceResponse(IceResponse::SUCCESS, $list));
            }
        );
	}

    public function getCount(DataQuery $query)
    {
        $table = $query->getTable();
        $nsTable = BaseService::getInstance()->getFullQualifiedModelClassName($table);
        /** @var BaseModel $obj */
        $obj = new $nsTable();

        // Count the same rows the listing returns (Active filter + search term).
        // Counting everything ('1 = 1') made `total` overshoot whenever
        // non-active rows exist, so clients paging until list >= total kept
        // fetching past the end of the list forever.
        $where = 'status = ?';
        $params = ['Active'];
        $term = $query->getSearchTerm();
        if (!empty($term)) {
            $where .= ' AND (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ?)';
            $like = '%' . $term . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        return $obj->Count($where, $params);
    }
}

