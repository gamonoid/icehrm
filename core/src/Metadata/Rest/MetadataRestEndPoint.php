<?php
namespace Metadata\Rest;

use Classes\BaseService;
use Classes\Data\Query\DataQuery;
use Classes\Data\Query\Filter;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Classes\SettingsManager;
use Modules\Common\Model\Module;
use Users\Common\Model\User;

class MetadataRestEndPoint extends RestEndPoint
{
    /**
     * Known extensions that can be detected for mobile app
     * Maps extension folder name to display info
     */
    private static $knownExtensions = [
        'leave' => [
            'code' => 'leave',
            'label' => 'Leave',
            'features' => ['leaves']
        ],
        'leave_and_performance' => [
            'code' => 'leave_and_performance',
            'label' => 'Leave & Performance',
            'features' => ['leaves', 'performance_reviews']
        ],
        'expenses' => [
            'code' => 'expenses',
            'label' => 'Expenses',
            'features' => ['expenses']
        ],
        'travel' => [
            'code' => 'travel',
            'label' => 'Travel Management',
            'features' => ['travel_requests']
        ],
        'training' => [
            'code' => 'training',
            'label' => 'Training',
            'features' => ['training', 'courses']
        ],
        'recruitment' => [
            'code' => 'recruitment',
            'label' => 'Recruitment',
            'features' => ['recruitment', 'job_postings']
        ],
        'documents' => [
            'code' => 'documents',
            'label' => 'Documents',
            'features' => ['documents']
        ],
        'payroll' => [
            'code' => 'payroll',
            'label' => 'Payroll',
            'features' => ['payroll', 'payslips']
        ],
        'demo-mode' => [
            'code' => 'demo-mode',
            'label' => 'Demo Mode',
            'features' => ['demo_data']
        ],
    ];

    public function getCurrency(User $user)
    {
        $query = new DataQuery('CurrencyType');
        $query->setLength(500);

        // Respect "System: Allowed Currencies" setting
        $allowedCurrenciesStr = SettingsManager::getInstance()->getSetting('System: Allowed Currencies');
        if (!empty($allowedCurrenciesStr)) {
            $allowedCurrencies = json_decode($allowedCurrenciesStr, true);
            if (!empty($allowedCurrencies) && is_array($allowedCurrencies)) {
                $query->addFilter(new Filter('id', $allowedCurrencies, 'in'));
            }
        }

        return $this->listByQuery($query);
    }

    public function getCountries(User $user)
    {
        $query = new DataQuery('Country');
        $query->setLength(500);
        return $this->listByQuery($query);
    }

    public function getMobileModules(User $user)
    {
        $mobileModules = [
            'leaves' => false,
            'attendance' => false,
            'staffdirectory' => false,
            'expenses' => false,
        ];

        foreach ($mobileModules as $key => $value) {
            $mobileModules[$key] = $this->isUserModuleEnabled($key);
        }

        return new IceResponse(IceResponse::SUCCESS, ['data' => $mobileModules]);
    }

    private function isUserModuleEnabled($name)
    {
        $module = new Module();
        $modules = $module->Find('name = ? and mod_group = ? and status = ?', [$name, 'user', 'Enabled']);

        BaseService::getInstance()->initializePro();

        return count($modules) > 0 && BaseService::getInstance()->isModuleEnabled('modules', $name);
    }

    /**
     * Get available extensions for mobile app
     * Returns a list of installed extensions with their status
     */
    public function getExtensions(User $user)
    {
        $extensions = [];
        if (!function_exists('iceExtensionRoots') && defined('APP_BASE_PATH')) {
            $resolver = APP_BASE_PATH . 'extensions/path-resolver.php';
            if (file_exists($resolver)) {
                require_once $resolver;
            }
        }
        // Scan both the free extensions/ and the paid extensions-pro/ roots.
        $roots = function_exists('iceExtensionRoots')
            ? iceExtensionRoots(APP_BASE_PATH . '../extensions/')
            : array(APP_BASE_PATH . '../extensions/');

        foreach ($roots as $extensionsPath) {
            if (!is_dir($extensionsPath)) {
                continue;
            }

            $dirs = scandir($extensionsPath);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }

            $fullPath = $extensionsPath . $dir;
            if (!is_dir($fullPath)) {
                continue;
            }

            // Check if this is an extension group (has group.json)
            $isGroup = file_exists($fullPath . '/group.json');

            // Check if it's a valid extension (has admin, user, or core subdirectory)
            $isValidExtension = $isGroup ||
                is_dir($fullPath . '/admin') ||
                is_dir($fullPath . '/user') ||
                is_dir($fullPath . '/core');

            if (!$isValidExtension) {
                continue;
            }

            $extData = [
                'code' => $dir,
                'installed' => true,
            ];

            // Add known extension metadata if available
            if (isset(self::$knownExtensions[$dir])) {
                $extData['label'] = self::$knownExtensions[$dir]['label'];
                $extData['features'] = self::$knownExtensions[$dir]['features'];
            } else {
                // For unknown extensions, use folder name as label
                $extData['label'] = ucwords(str_replace(['_', '-'], ' ', $dir));
                $extData['features'] = [];
            }

            // Try to read version info
            $versionPath = $fullPath . '/version.json';
            if (file_exists($versionPath)) {
                $versionData = json_decode(file_get_contents($versionPath), true);
                if ($versionData) {
                    $extData['version'] = $versionData['version'] ?? null;
                }
            }

            // Try to read group.json for group label
            if ($isGroup) {
                $groupJsonPath = $fullPath . '/group.json';
                $groupData = json_decode(file_get_contents($groupJsonPath), true);
                if ($groupData && isset($groupData['label'])) {
                    $extData['label'] = $groupData['label'];
                }
                if ($groupData && isset($groupData['extensions'])) {
                    $extData['features'] = $groupData['extensions'];
                }
                $extData['isGroup'] = true;
            } else {
                $extData['isGroup'] = false;
            }

            $extensions[] = $extData;
            }
        }

        return new IceResponse(IceResponse::SUCCESS, ['data' => $extensions]);
    }
}
