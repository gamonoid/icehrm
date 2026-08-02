<?php

namespace Classes;

/**
 * MenuAreaService
 *
 * "Areas" are a high-level grouping layered over the menu so the SPA shell can
 * show one functional domain at a time (Home, People, Time and Work, Leave, …)
 * instead of the full ~50-item menu. The shell renders an area selector; picking
 * an area filters the left menu to that area's items.
 *
 * Source of truth precedence for an item's area:
 *   1. the module/extension's `meta.json` "area" field (carried onto the menu
 *      item by core/modules.php + ExtensionManager) — the per-module override
 *      and the mechanism a NEW extension uses to join or define an area;
 *   2. a built-in default map keyed by the item's route (so every current module
 *      lands in a sensible area before its meta.json is migrated);
 *   3. the fallback area ("more") so nothing ever disappears.
 *
 * The legacy app never reads "area", so adding it to meta.json is inert there.
 */
class MenuAreaService
{
    const FALLBACK = 'more';

    /**
     * The ordered area registry. `icon` is a logical name the shell maps to an
     * antd icon. Extensions may also declare brand-new areas via meta.json (see
     * customAreas()); those are appended after these.
     */
    private static function baseAreas()
    {
        return [
            ['id' => 'people',         'label' => 'People',                  'icon' => 'people'],
            ['id' => 'time_and_work',  'label' => 'Time and Work',           'icon' => 'time'],
            ['id' => 'leave',          'label' => 'Leave',                   'icon' => 'leave'],
            ['id' => 'pay',            'label' => 'Pay & Expenses',          'icon' => 'pay'],
            ['id' => 'recruitment',    'label' => 'Recruitment',             'icon' => 'recruitment'],
            ['id' => 'learning',       'label' => 'Learning',                'icon' => 'learning'],
            ['id' => 'performance',    'label' => 'Performance',             'icon' => 'performance'],
            ['id' => 'documents',      'label' => 'Documents',               'icon' => 'documents'],
            ['id' => 'reports',        'label' => 'Reports & Insights',      'icon' => 'reports'],
            ['id' => 'configuration',  'label' => 'Company Setup',           'icon' => 'configuration'],
            ['id' => 'system',         'label' => 'System',                  'icon' => 'system'],
            ['id' => self::FALLBACK,   'label' => 'More',                    'icon' => 'more'],
        ];
    }

    /**
     * Built-in default area per route key ("g::n"). Keeps every shipped module in
     * the right area until its meta.json carries an explicit "area". meta.json
     * always wins over this map.
     */
    private static function defaultMap()
    {
        return [
            // --- admin modules ---
            'admin::dashboard' => 'people',
            'admin::company_structure' => 'people',
            'admin::jobs' => 'configuration',
            'admin::qualifications' => 'configuration',
            'admin::projects' => 'time_and_work',
            'admin::custom_fields' => 'configuration',
            'admin::audit' => 'system',
            'admin::employees' => 'people',
            'admin::employeehistory' => 'people',
            'extension::tasks|admin' => 'time_and_work',
            'admin::documents' => 'documents',
            'admin::attendance' => 'time_and_work',
            'admin::performance' => 'performance',
            'admin::leaves' => 'leave',
            'extension::expenses|admin' => 'pay',
            'extension::learn|admin' => 'learning',
            'admin::training' => 'learning',
            'admin::travel' => 'pay',
            'admin::overtime' => 'time_and_work',
            'admin::loans' => 'pay',
            'extension::company_assets|admin' => 'configuration',
            'extension::esign|admin' => 'documents',
            'extension::team|admin' => 'people',
            'extension::advance_reports|admin' => 'reports',
            'admin::settings' => 'system',
            'admin::users' => 'people',
            'admin::modules' => 'system',
            'admin::permissions' => 'system',
            'admin::metadata' => 'configuration',
            'admin::fieldnames' => 'configuration',
            'admin::connection' => 'system',
            'extension::demo-mode|admin' => 'system',
            'extension::insights|admin' => 'reports',
            'admin::salary' => 'pay',
            'extension::payroll_config|admin' => 'pay',
            'extension::jobsetup|admin' => 'recruitment',
            'extension::jobpositions|admin' => 'recruitment',
            'extension::candidates|admin' => 'recruitment',
            'extension::marketplace|admin' => 'system',

            // --- user (employee) modules ---
            'modules::dashboard' => 'people',
            'modules::employees' => 'people',
            'modules::qualifications' => 'people',
            'modules::dependents' => 'people',
            'modules::emergency_contact' => 'people',
            'extension::team|user' => 'people',
            'extension::company_overview|user' => 'people',
            'extension::directory|user' => 'people',
            'modules::leaves' => 'leave',
            'modules::leavecal' => 'leave',
            'modules::attendance' => 'time_and_work',
            'modules::time_sheets' => 'time_and_work',
            'modules::overtime' => 'time_and_work',
            'extension::tasks|user' => 'time_and_work',
            'modules::documents' => 'documents',
            'extension::esign|user' => 'documents',
            'extension::learn|user' => 'learning',
            'modules::training' => 'learning',
            'modules::performance' => 'performance',
            'modules::travel' => 'pay',
            'extension::expenses|user' => 'pay',
            'modules::loans' => 'pay',
            'extension::advance_reports|user' => 'reports',
        ];
    }

    /** The default area id for a route, or the fallback area. */
    public static function defaultAreaFor($g, $n)
    {
        $map = self::defaultMap();
        $key = $g . '::' . $n;
        return isset($map[$key]) ? $map[$key] : self::FALLBACK;
    }

    /**
     * Resolve the area for a built menu item: its explicit meta.json "area" wins,
     * else the default map, else the fallback.
     *
     * @param array  $item the built menu item (may carry 'area' from meta.json)
     * @param string $g    resolved link group
     * @param string $n    resolved link name
     */
    public static function resolveArea($item, $g, $n)
    {
        if (!empty($item['area']) && is_string($item['area'])) {
            return $item['area'];
        }
        return self::defaultAreaFor($g, $n);
    }

    /**
     * The area registry for the shell. Includes the built-in areas plus any extra
     * area ids referenced by items but not built in (an extension defining its own
     * area), labelled from the supplied $custom map (id => {label, icon}) or a
     * humanised id fallback.
     *
     * @param array $referencedIds  area ids actually used by the user's items
     * @param array $custom         id => ['label'=>, 'icon'=>] from meta.json
     */
    public static function areas(array $referencedIds = [], array $custom = [])
    {
        $areas = self::baseAreas();
        $known = [];
        foreach ($areas as $a) {
            $known[$a['id']] = true;
        }
        foreach ($referencedIds as $id) {
            if (empty($id) || isset($known[$id])) {
                continue;
            }
            $known[$id] = true;
            $label = isset($custom[$id]['label']) ? $custom[$id]['label']
                : ucwords(str_replace(['_', '-'], ' ', $id));
            $icon = isset($custom[$id]['icon']) ? $custom[$id]['icon'] : 'more';
            // Insert custom areas before the trailing fallback "More".
            array_splice($areas, count($areas) - 1, 0, [[
                'id' => $id, 'label' => $label, 'icon' => $icon,
            ]]);
        }
        return $areas;
    }
}
