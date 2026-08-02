<?php

namespace Company_assetsAdmin;

use Classes\BaseService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\PermissionManager;
use Classes\RestEndPoint;
use Company_assetsAdmin\Common\Model\AssetType;
use Company_assetsAdmin\Common\Model\CompanyAsset;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;

class ApiController extends IceApiController
{
    private function hasAccess($model, $action)
    {
        $access = PermissionManager::checkGeneralAccess($model);
        return is_array($access) && in_array($action, $access);
    }

    public function registerEndPoints()
    {
        // Asset Types endpoints
        self::register(
            REST_API_PATH . 'company_assets/asset-types',
            self::GET,
            function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                if (!$this->hasAccess(new AssetType(), 'get')) {
                    $restEndpoint->sendErrorResponse('Access denied', 403);
                    return;
                }

                $assetType = new AssetType();
                $types = $assetType->Find("1=1 ORDER BY name ASC");

                $result = array_map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'description' => $type->description,
                        'attachment' => $type->attachment,
                        'custom_fields' => $type->custom_fields ? json_decode($type->custom_fields, true) : [],
                    ];
                }, $types);

                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $result));
            }
        );

        self::register(
            REST_API_PATH . 'company_assets/asset-types/(:num)',
            self::GET,
            function ($id) {
                $restEndpoint = new RestEndPoint();
                if (!$this->hasAccess(new AssetType(), 'element')) {
                    $restEndpoint->sendErrorResponse('Access denied', 403);
                    return;
                }

                $assetType = new AssetType();
                $assetType->Load("id = ?", [$id]);

                if (empty($assetType->id)) {
                    $restEndpoint->sendErrorResponse('Asset type not found', 404);
                    return;
                }

                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, [
                    'id' => $assetType->id,
                    'name' => $assetType->name,
                    'description' => $assetType->description,
                    'attachment' => $assetType->attachment,
                    'custom_fields' => $assetType->custom_fields ? json_decode($assetType->custom_fields, true) : [],
                ]));
            }
        );

        self::register(
            REST_API_PATH . 'company_assets/asset-types',
            self::POST,
            function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                if (!$this->hasAccess(new AssetType(), 'save')) {
                    $restEndpoint->sendErrorResponse('Access denied', 403);
                    return;
                }

                $data = $restEndpoint->getRequestBody();
                $assetType = new AssetType();

                if (!empty($data['id'])) {
                    $assetType->Load("id = ?", [$data['id']]);
                    if (empty($assetType->id)) {
                        $restEndpoint->sendErrorResponse('Asset type not found', 404);
                        return;
                    }
                }

                $assetType->name = $data['name'] ?? $assetType->name;
                $assetType->description = $data['description'] ?? null;
                $assetType->attachment = $data['attachment'] ?? null;

                // Handle custom_fields - store as JSON
                if (isset($data['custom_fields'])) {
                    $assetType->custom_fields = is_array($data['custom_fields'])
                        ? json_encode($data['custom_fields'])
                        : $data['custom_fields'];
                }

                if (empty($assetType->id)) {
                    $assetType->created = date('Y-m-d H:i:s');
                }
                $assetType->updated = date('Y-m-d H:i:s');

                $ok = $assetType->Save();
                if (!$ok) {
                    $restEndpoint->sendErrorResponse('Failed to save asset type');
                    return;
                }

                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, [
                    'id' => $assetType->id,
                    'name' => $assetType->name,
                    'description' => $assetType->description,
                    'custom_fields' => $assetType->custom_fields ? json_decode($assetType->custom_fields, true) : [],
                ]));
            }
        );

        self::register(
            REST_API_PATH . 'company_assets/asset-types/(:num)',
            self::DELETE,
            function ($id) {
                $restEndpoint = new RestEndPoint();
                if (!$this->hasAccess(new AssetType(), 'delete')) {
                    $restEndpoint->sendErrorResponse('Access denied', 403);
                    return;
                }

                // Check if any assets are using this type
                $asset = new CompanyAsset();
                $usages = $asset->Find("type = ?", [$id]);
                if (!empty($usages)) {
                    $restEndpoint->sendResponse(new IceResponse(
                        IceResponse::ERROR,
                        'Cannot delete. This asset type is used by ' . count($usages) . ' asset(s).'
                    ));
                    return;
                }

                $assetType = new AssetType();
                $assetType->Load("id = ?", [$id]);

                if (empty($assetType->id)) {
                    $restEndpoint->sendErrorResponse('Asset type not found', 404);
                    return;
                }

                $assetType->Delete();
                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, ['deleted' => true]));
            }
        );

        // Company Assets endpoints
        self::register(
            REST_API_PATH . 'company_assets/assets',
            self::GET,
            function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                if (!$this->hasAccess(new CompanyAsset(), 'get')) {
                    $restEndpoint->sendErrorResponse('Access denied', 403);
                    return;
                }

                $params = $_GET;
                $where = "1=1";
                $bindings = [];

                // Search filter
                if (!empty($params['search'])) {
                    $search = '%' . $params['search'] . '%';
                    $where .= " AND (code LIKE ? OR name LIKE ? OR serial_number LIKE ?)";
                    $bindings[] = $search;
                    $bindings[] = $search;
                    $bindings[] = $search;
                }

                // Status filter
                if (!empty($params['status'])) {
                    $where .= " AND status = ?";
                    $bindings[] = $params['status'];
                }

                // Type filter
                if (!empty($params['type'])) {
                    $where .= " AND type = ?";
                    $bindings[] = $params['type'];
                }

                // Employee filter
                if (!empty($params['employee'])) {
                    $where .= " AND employee = ?";
                    $bindings[] = $params['employee'];
                }

                // Department filter
                if (!empty($params['department'])) {
                    $where .= " AND department = ?";
                    $bindings[] = $params['department'];
                }

                $where .= " ORDER BY code ASC";

                $asset = new CompanyAsset();
                $assets = $asset->Find($where, $bindings);

                $result = [];
                foreach ($assets as $a) {
                    $result[] = $this->formatAsset($a);
                }

                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $result));
            }
        );

        self::register(
            REST_API_PATH . 'company_assets/assets/(:num)',
            self::GET,
            function ($id) {
                $restEndpoint = new RestEndPoint();
                if (!$this->hasAccess(new CompanyAsset(), 'element')) {
                    $restEndpoint->sendErrorResponse('Access denied', 403);
                    return;
                }

                $asset = new CompanyAsset();
                $asset->Load("id = ?", [$id]);

                if (empty($asset->id)) {
                    $restEndpoint->sendErrorResponse('Asset not found', 404);
                    return;
                }

                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->formatAsset($asset)));
            }
        );

        self::register(
            REST_API_PATH . 'company_assets/assets',
            self::POST,
            function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                if (!$this->hasAccess(new CompanyAsset(), 'save')) {
                    $restEndpoint->sendErrorResponse('Access denied', 403);
                    return;
                }

                $data = $restEndpoint->getRequestBody();
                $asset = new CompanyAsset();

                if (!empty($data['id'])) {
                    $asset->Load("id = ?", [$data['id']]);
                    if (empty($asset->id)) {
                        $restEndpoint->sendErrorResponse('Asset not found', 404);
                        return;
                    }
                }

                // Validate required fields
                if (empty($data['code'])) {
                    $restEndpoint->sendResponse(new IceResponse(IceResponse::ERROR, 'Asset code is required'));
                    return;
                }

                // Check for duplicate code
                $existing = new CompanyAsset();
                $existing->Load("code = ? AND id != ?", [$data['code'], $data['id'] ?? 0]);
                if (!empty($existing->id)) {
                    $restEndpoint->sendResponse(new IceResponse(IceResponse::ERROR, 'Asset code already exists'));
                    return;
                }

                $asset->code = $data['code'];
                $asset->name = $data['name'] ?? null;
                $asset->type = !empty($data['type']) ? $data['type'] : null;
                $asset->serial_number = $data['serial_number'] ?? null;
                $asset->employee = !empty($data['employee']) ? $data['employee'] : null;

                // Auto-set status based on employee assignment
                $requestedStatus = $data['status'] ?? 'Available';
                if (!empty($asset->employee) && $requestedStatus === 'Available') {
                    $asset->status = 'Assigned';
                } elseif (empty($asset->employee) && $requestedStatus === 'Assigned') {
                    $asset->status = 'Available';
                } else {
                    $asset->status = $requestedStatus;
                }
                $asset->department = !empty($data['department']) ? $data['department'] : null;
                $asset->purchase_date = !empty($data['purchase_date']) ? $data['purchase_date'] : null;
                $asset->purchase_price = !empty($data['purchase_price']) ? $data['purchase_price'] : null;
                $asset->warranty_end = !empty($data['warranty_end']) ? $data['warranty_end'] : null;
                $asset->location = $data['location'] ?? null;
                $asset->notes = $data['notes'] ?? null;
                $asset->description = $data['description'] ?? null;
                $asset->attachment = $data['attachment'] ?? null;

                // Handle custom_field_values - store as JSON
                if (isset($data['custom_field_values'])) {
                    $asset->custom_field_values = is_array($data['custom_field_values'])
                        ? json_encode($data['custom_field_values'])
                        : $data['custom_field_values'];
                }

                if (empty($asset->id)) {
                    $asset->created = date('Y-m-d H:i:s');
                }
                $asset->updated = date('Y-m-d H:i:s');

                $ok = $asset->Save();
                if (!$ok) {
                    $restEndpoint->sendErrorResponse('Failed to save asset');
                    return;
                }

                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->formatAsset($asset)));
            }
        );

        self::register(
            REST_API_PATH . 'company_assets/assets/(:num)',
            self::DELETE,
            function ($id) {
                $restEndpoint = new RestEndPoint();
                if (!$this->hasAccess(new CompanyAsset(), 'delete')) {
                    $restEndpoint->sendErrorResponse('Access denied', 403);
                    return;
                }

                $asset = new CompanyAsset();
                $asset->Load("id = ?", [$id]);

                if (empty($asset->id)) {
                    $restEndpoint->sendErrorResponse('Asset not found', 404);
                    return;
                }

                $asset->Delete();
                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, ['deleted' => true]));
            }
        );

        // Helper endpoints
        self::register(
            REST_API_PATH . 'company_assets/employees',
            self::GET,
            function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $employee = new Employee();
                $employees = $employee->Find("status = ? ORDER BY first_name, last_name", ['Active']);

                $result = array_map(function ($emp) {
                    return [
                        'id' => $emp->id,
                        'name' => trim($emp->first_name . ' ' . $emp->last_name),
                        'employee_id' => $emp->employee_id,
                    ];
                }, $employees);

                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $result));
            }
        );

        self::register(
            REST_API_PATH . 'company_assets/departments',
            self::GET,
            function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $dept = new CompanyStructure();
                $departments = $dept->Find("1=1 ORDER BY title");

                $result = array_map(function ($d) {
                    return [
                        'id' => $d->id,
                        'title' => $d->title,
                    ];
                }, $departments);

                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $result));
            }
        );

        // Stats endpoint
        self::register(
            REST_API_PATH . 'company_assets/stats',
            self::GET,
            function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $asset = new CompanyAsset();

                $total = count($asset->Find("1=1"));
                $available = count($asset->Find("status = ?", ['Available']));
                $assigned = count($asset->Find("status = ?", ['Assigned']));
                $inRepair = count($asset->Find("status = ?", ['In Repair']));
                $retired = count($asset->Find("status = ?", ['Retired']));

                $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, [
                    'total' => $total,
                    'available' => $available,
                    'assigned' => $assigned,
                    'inRepair' => $inRepair,
                    'retired' => $retired,
                ]));
            }
        );
    }

    private function formatAsset($asset)
    {
        $type = null;
        $typeCustomFields = [];
        if (!empty($asset->type)) {
            $assetType = new AssetType();
            $assetType->Load("id = ?", [$asset->type]);
            if (!empty($assetType->id)) {
                $type = ['id' => $assetType->id, 'name' => $assetType->name];
                $typeCustomFields = $assetType->custom_fields
                    ? json_decode($assetType->custom_fields, true)
                    : [];
            }
        }

        $employee = null;
        if (!empty($asset->employee)) {
            $emp = new Employee();
            $emp->Load("id = ?", [$asset->employee]);
            if (!empty($emp->id)) {
                $employee = [
                    'id' => $emp->id,
                    'name' => trim($emp->first_name . ' ' . $emp->last_name),
                    'employee_id' => $emp->employee_id,
                ];
            }
        }

        $department = null;
        if (!empty($asset->department)) {
            $dept = new CompanyStructure();
            $dept->Load("id = ?", [$asset->department]);
            if (!empty($dept->id)) {
                $department = ['id' => $dept->id, 'title' => $dept->title];
            }
        }

        return [
            'id' => $asset->id,
            'code' => $asset->code,
            'name' => $asset->name,
            'type' => $type,
            'type_custom_fields' => $typeCustomFields,
            'serial_number' => $asset->serial_number,
            'status' => $asset->status,
            'employee' => $employee,
            'department' => $department,
            'purchase_date' => $asset->purchase_date,
            'purchase_price' => $asset->purchase_price,
            'warranty_end' => $asset->warranty_end,
            'location' => $asset->location,
            'notes' => $asset->notes,
            'description' => $asset->description,
            'attachment' => $asset->attachment,
            'custom_field_values' => $asset->custom_field_values
                ? json_decode($asset->custom_field_values, true)
                : [],
            'created' => $asset->created,
            'updated' => $asset->updated,
        ];
    }
}
