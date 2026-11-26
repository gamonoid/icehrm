<?php

namespace CompanyOnboardingAdmin;

use Utils\LogManager;

/**
 * Work week and public holidays for the setup wizard (tables of the leave module).
 * The wizard edits the company-wide rows (country NULL); per-country rules stay in the
 * leave module.
 *
 * Public holidays can be loaded from Nager.Date (https://date.nager.at), a free public
 * holiday service. Only the country code and year are sent.
 */
class CalendarService
{
    const DAYS = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
    const DAY_STATUSES = array('Full Day', 'Half Day', 'Non-working Day');

    public static function available()
    {
        return Db::tableExists('WorkDays') && Db::tableExists('HoliDays');
    }

    public function get($year)
    {
        $days = array();
        foreach (self::DAYS as $day) {
            $days[$day] = in_array($day, array('Saturday', 'Sunday'), true) ? 'Non-working Day' : 'Full Day';
        }
        foreach (Db::rows('SELECT name, status FROM WorkDays WHERE country IS NULL') as $row) {
            if (isset($days[$row['name']])) {
                $days[$row['name']] = $row['status'];
            }
        }
        $holidays = array();
        foreach (Db::rows(
            'SELECT id, name, dateh, status FROM HoliDays WHERE country IS NULL AND YEAR(dateh) = ? ORDER BY dateh',
            array((int) $year)
        ) as $row) {
            $holidays[] = array('id' => (int) $row['id'], 'name' => $row['name'], 'date' => $row['dateh'], 'status' => $row['status']);
        }
        return array('year' => (int) $year, 'workdays' => $days, 'holidays' => $holidays);
    }

    public function saveWorkdays(array $input)
    {
        foreach (self::DAYS as $day) {
            if (!isset($input[$day]) || !in_array($input[$day], self::DAY_STATUSES, true)) {
                continue;
            }
            $rows = Db::rows('SELECT id FROM WorkDays WHERE name = ? AND country IS NULL', array($day));
            if (empty($rows)) {
                Db::db()->Execute('INSERT INTO WorkDays (name, status, country) VALUES (?, ?, NULL)', array($day, $input[$day]));
            } else {
                Db::db()->Execute('UPDATE WorkDays SET status = ? WHERE id = ?', array($input[$day], (int) $rows[0]['id']));
            }
        }
        return null;
    }

    public function addHoliday(array $input)
    {
        $name = isset($input['name']) && is_scalar($input['name']) ? mb_substr(trim((string) $input['name']), 0, 100) : '';
        $date = isset($input['date']) ? (string) $input['date'] : '';
        $status = isset($input['status']) && $input['status'] === 'Half Day' ? 'Half Day' : 'Full Day';
        if ($name === '') {
            return 'Enter a name.';
        }
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return 'Choose a date.';
        }
        if (!empty(Db::rows('SELECT id FROM HoliDays WHERE dateh = ? AND country IS NULL', array($date)))) {
            return 'There is already a holiday on this date.';
        }
        Db::db()->Execute('INSERT INTO HoliDays (name, dateh, status, country) VALUES (?, ?, ?, NULL)', array($name, $date, $status));
        return null;
    }

    public function removeHoliday($id)
    {
        Db::db()->Execute('DELETE FROM HoliDays WHERE id = ? AND country IS NULL', array((int) $id));
        return null;
    }

    /** @return array [added count, error|null] */
    public function importHolidays($countryCode, $year)
    {
        if (!preg_match('/^[A-Z]{2}$/', (string) $countryCode)) {
            return array(0, 'Save the company country first.');
        }
        $year = (int) $year;
        if ($year < 2000 || $year > 2100) {
            return array(0, 'Choose a year.');
        }
        $url = sprintf('https://date.nager.at/api/v3/PublicHolidays/%d/%s', $year, $countryCode);
        $json = $this->fetch($url);
        if ($json === null) {
            return array(0, 'Public holidays could not be loaded. Add them yourself below.');
        }
        $list = json_decode($json, true);
        if (!is_array($list) || empty($list)) {
            return array(0, 'No public holidays are available for this country. Add them yourself below.');
        }
        $added = 0;
        foreach ($list as $holiday) {
            if (empty($holiday['date']) || empty($holiday['localName'])) {
                continue;
            }
            // Regional holidays (only some states or counties) are left out.
            if (isset($holiday['global']) && $holiday['global'] === false) {
                continue;
            }
            $name = !empty($holiday['name']) ? $holiday['name'] : $holiday['localName'];
            if ($this->addHoliday(array('name' => $name, 'date' => $holiday['date'])) === null) {
                $added++;
            }
        }
        return array($added, null);
    }

    private function fetch($url)
    {
        try {
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
                $body = curl_exec($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                return ($body !== false && $code === 200) ? $body : null;
            }
            $context = stream_context_create(array('http' => array('timeout' => 10, 'header' => "Accept: application/json\r\n")));
            $body = @file_get_contents($url, false, $context);
            return $body === false ? null : $body;
        } catch (\Throwable $e) {
            LogManager::getInstance()->error('Company onboarding: holiday import failed: ' . $e->getMessage());
            return null;
        }
    }
}
