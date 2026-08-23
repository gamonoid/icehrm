<?php

namespace Leaves\Common;

/**
 * Provides public-holiday data bundled with the leave module.
 *
 * The data was sourced from the free Nager.Date public-holiday API
 * (https://date.nager.at) and baked into per-country JSON files under
 * Data/holidays/ so the application never has to call an external service at
 * runtime. Coverage: every Nager.Date country, years 2022–2035.
 *
 *   Data/holidays/_countries.json -> [{ "code": "DE", "name": "Germany" }, ...]
 *   Data/holidays/{CODE}.json     -> { "2022": [{ "date": "...", "name": "..." }], ... }
 */
class PublicHolidayProvider
{
    const MIN_YEAR = 2022;
    const MAX_YEAR = 2035;

    /** @var PublicHolidayProvider|null */
    private static $instance = null;

    /** @var string */
    private $dataDir;

    /** @var array|null */
    private $countries = null;

    private function __construct()
    {
        $this->dataDir = __DIR__ . '/Data/holidays';
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getMinYear()
    {
        return self::MIN_YEAR;
    }

    public function getMaxYear()
    {
        return self::MAX_YEAR;
    }

    /**
     * @return array list of [ 'code' => 'DE', 'name' => 'Germany' ]
     */
    public function getAvailableCountries()
    {
        if ($this->countries === null) {
            $file = $this->dataDir . '/_countries.json';
            $this->countries = $this->readJson($file) ?: [];
        }
        return $this->countries;
    }

    /**
     * Public holidays for an ISO 3166-1 alpha-2 country code and a year.
     *
     * @return array list of [ 'date' => 'YYYY-MM-DD', 'name' => '...' ]
     */
    public function getHolidays($code, $year)
    {
        $code = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) $code));
        $year = (int) $year;
        if ($code === '' || $year < self::MIN_YEAR || $year > self::MAX_YEAR) {
            return [];
        }

        $file = $this->dataDir . '/' . $code . '.json';
        $data = $this->readJson($file);
        if (!is_array($data) || !isset($data[(string) $year]) || !is_array($data[(string) $year])) {
            return [];
        }
        return $data[(string) $year];
    }

    private function readJson($file)
    {
        if (!is_file($file)) {
            return null;
        }
        $raw = file_get_contents($file);
        if ($raw === false || $raw === '') {
            return null;
        }
        return json_decode($raw, true);
    }
}
