<?php

namespace CompanyOnboardingAdmin;

use Classes\BaseService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Utils\LogManager;

/**
 * REST endpoints of the company setup wizard. Admin only.
 *
 *   GET    company-onboarding/setup                  steps, progress, counts
 *   POST   company-onboarding/setup/steps/(:any)     {status: Done|Skipped|NotStarted}
 *   GET    company-onboarding/lookups                countries, time zones
 *   GET    company-onboarding/company                company details
 *   POST   company-onboarding/company                save company details
 *   GET    company-onboarding/structure              all units
 *   POST   company-onboarding/structure              add a unit
 *   POST   company-onboarding/structure/(:num)       update a unit
 *   DELETE company-onboarding/structure/(:num)       remove an empty unit
 *   POST   company-onboarding/structure-preset/(:any) add a preset's offices and departments
 *   GET    company-onboarding/lists/(:any)           job_titles | employment | locations | leave_types
 *   POST   company-onboarding/lists/(:any)           {entries: [{name, days}]} add, skipping duplicates
 *   POST   company-onboarding/lists/(:any)/(:num)    rename (and days for leave types)
 *   DELETE company-onboarding/lists/(:any)/(:num)    remove an unused entry
 *   GET    company-onboarding/calendar               work week and this year's holidays (&year=)
 *   POST   company-onboarding/workdays               {Monday: 'Full Day', ...}
 *   POST   company-onboarding/holidays               add one {name, date, status}
 *   POST   company-onboarding/holidays-import        {year} public holidays for the company country
 *   DELETE company-onboarding/holidays/(:num)
 *
 * Only GET, POST and DELETE: the shared IceApiClient has no PUT.
 */
class ApiController extends IceApiController
{
    public function registerEndPoints()
    {
        $this->route(self::GET, 'company-onboarding/setup', function ($rest) {
            $this->ok($rest, (new SetupService())->summary());
        });

        $this->route(self::POST, 'company-onboarding/setup/steps/(:any)', function ($rest, $user, $key) {
            $body = $this->body($rest);
            $status = isset($body['status']) ? (string) $body['status'] : '';
            $setup = new SetupService();
            $error = $setup->setStatus((string) $key, $status, $user);
            if ($error !== null) {
                $this->fail($rest, $error);
                return;
            }
            $this->ok($rest, $setup->summary());
        });

        $this->route(self::GET, 'company-onboarding/lookups', function ($rest) {
            $countries = array();
            foreach (Db::rows('SELECT code, name FROM Country ORDER BY name') as $row) {
                $countries[] = array('value' => $row['code'], 'label' => $row['name']);
            }
            $this->ok($rest, array(
                'countries' => $countries,
                'timezones' => \DateTimeZone::listIdentifiers(),
                'unit_types' => CompanyService::UNIT_TYPES,
            ));
        });

        $this->route(self::GET, 'company-onboarding/company', function ($rest) {
            $this->ok($rest, (new CompanyService())->getCompany());
        });

        $this->route(self::POST, 'company-onboarding/company', function ($rest) {
            // Field errors come back as {ok: false, errors: {field: message}}.
            $errors = (new CompanyService())->saveCompany($this->body($rest));
            if (!empty($errors)) {
                $this->ok($rest, array('ok' => false, 'errors' => $errors));
                return;
            }
            $this->ok($rest, array('ok' => true, 'company' => (new CompanyService())->getCompany()));
        });

        $this->route(self::GET, 'company-onboarding/structure', function ($rest) {
            $this->ok($rest, (new CompanyService())->units());
        });

        $this->route(self::POST, 'company-onboarding/structure', function ($rest) {
            list($id, $error) = (new CompanyService())->addUnit($this->body($rest));
            $this->result($rest, array('id' => $id), $error);
        });

        $this->route(self::POST, 'company-onboarding/structure/(:num)', function ($rest, $user, $id) {
            $error = (new CompanyService())->updateUnit($id, $this->body($rest));
            $this->result($rest, array('id' => (int) $id), $error);
        });

        $this->route(self::DELETE, 'company-onboarding/structure/(:num)', function ($rest, $user, $id) {
            $error = (new CompanyService())->deleteUnit($id);
            $this->result($rest, array('id' => (int) $id), $error);
        });

        $this->route(self::POST, 'company-onboarding/structure-preset/(:any)', function ($rest, $user, $key) {
            $error = (new CompanyService())->applyPreset((string) $key);
            $this->result($rest, (new CompanyService())->units(), $error);
        });

        $this->registerListRoutes();
        $this->registerCalendarRoutes();
    }

    private function registerListRoutes()
    {
        $this->route(self::GET, 'company-onboarding/lists/(:any)', function ($rest, $user, $key) {
            if (!LookupService::exists($key)) {
                $this->fail($rest, 'Unknown list.');
                return;
            }
            $this->ok($rest, (new LookupService())->items($key));
        });

        $this->route(self::POST, 'company-onboarding/lists/(:any)', function ($rest, $user, $key) {
            if (!LookupService::exists($key)) {
                $this->fail($rest, 'Unknown list.');
                return;
            }
            $body = $this->body($rest);
            $entries = isset($body['entries']) && is_array($body['entries']) ? array_slice($body['entries'], 0, 200) : array();
            $service = new LookupService();
            $added = $service->add($key, $entries);
            $this->ok($rest, array('added' => $added, 'items' => $service->items($key)));
        });

        $this->route(self::POST, 'company-onboarding/lists/(:any)/(:num)', function ($rest, $user, $key, $id) {
            if (!LookupService::exists($key)) {
                $this->fail($rest, 'Unknown list.');
                return;
            }
            $error = (new LookupService())->update($key, $id, $this->body($rest));
            $this->result($rest, array('id' => (int) $id), $error);
        });

        $this->route(self::DELETE, 'company-onboarding/lists/(:any)/(:num)', function ($rest, $user, $key, $id) {
            if (!LookupService::exists($key)) {
                $this->fail($rest, 'Unknown list.');
                return;
            }
            $error = (new LookupService())->remove($key, $id);
            $this->result($rest, array('id' => (int) $id), $error);
        });
    }

    private function registerCalendarRoutes()
    {
        $this->route(self::GET, 'company-onboarding/calendar', function ($rest) {
            if (!CalendarService::available()) {
                $this->fail($rest, 'The leave module is not installed.');
                return;
            }
            $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
            $this->ok($rest, (new CalendarService())->get($year));
        });

        $this->route(self::POST, 'company-onboarding/workdays', function ($rest) {
            $this->result($rest, array(), (new CalendarService())->saveWorkdays($this->body($rest)));
        });

        $this->route(self::POST, 'company-onboarding/holidays', function ($rest) {
            $this->result($rest, array(), (new CalendarService())->addHoliday($this->body($rest)));
        });

        $this->route(self::POST, 'company-onboarding/holidays-import', function ($rest) {
            $body = $this->body($rest);
            $company = (new CompanyService())->getCompany();
            list($added, $error) = (new CalendarService())->importHolidays(
                $company['country'],
                isset($body['year']) ? (int) $body['year'] : (int) date('Y')
            );
            $this->result($rest, array('added' => $added), $error);
        });

        $this->route(self::DELETE, 'company-onboarding/holidays/(:num)', function ($rest, $user, $id) {
            $this->result($rest, array(), (new CalendarService())->removeHoliday($id));
        });
    }

    /**
     * Register an Admin-only route; the handler gets ($rest, $user, ...pathParams).
     * Unexpected errors become a 500 with a generic message.
     */
    private function route($method, $path, callable $handler)
    {
        self::register(REST_API_PATH . $path, $method, function () use ($handler, $path) {
            $rest = new RestEndPoint();
            $user = BaseService::getInstance()->getCurrentUser();
            if (empty($user) || empty($user->id)) {
                $user = $rest->getCurrentUser();
            }
            if (empty($user) || empty($user->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Not authenticated', 401));
                return;
            }
            if (!SetupService::isAdmin($user)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Only admins can set up the company.', 403));
                return;
            }
            $args = array_merge(array($rest, $user), func_get_args());
            try {
                call_user_func_array($handler, $args);
            } catch (\Throwable $e) {
                LogManager::getInstance()->error('Company onboarding API ' . $path . ': ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Something went wrong. Please try again.', 500));
            }
        });
    }

    private function body(RestEndPoint $rest)
    {
        $body = $rest->getRequestBody();
        return is_array($body) ? $body : array();
    }

    private function ok(RestEndPoint $rest, $data)
    {
        $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $data));
    }

    private function fail(RestEndPoint $rest, $message)
    {
        $rest->sendResponse(new IceResponse(IceResponse::ERROR, $message ?: 'The request could not be completed.', 400));
    }

    private function result(RestEndPoint $rest, $data, $error)
    {
        if ($error !== null) {
            $this->fail($rest, $error);
            return;
        }
        $this->ok($rest, $data);
    }
}
