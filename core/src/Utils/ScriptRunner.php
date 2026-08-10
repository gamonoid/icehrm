<?php

namespace Utils;

use Utils\Js\JsError;
use Utils\Js\JsSandbox;

/**
 * Evaluates the JavaScript functions attached to payroll columns and data-import
 * transforms.
 *
 * The public contract is unchanged from the node/vm2 implementation this replaces —
 * same signature, same "script may arrive base64-encoded", same string return, same
 * empty string on failure — so PayrollActionManager, AbstractDataImporter and the
 * payroll_config validate-script endpoint needed no changes.
 *
 * What changed is underneath: the script is now evaluated in-process by a PHP
 * interpreter (Utils\Js\JsSandbox) instead of being shelled out to
 * `node core/execute/execute.js` and run inside vm2. See JsSandbox for why.
 *
 * The empty-string-on-error behaviour is preserved deliberately: callers treat it as
 * "no value" and a stricter contract would change payroll output. It is no longer
 * SILENT though — the reason is logged, which is what was missing when a production
 * image without node made every scripted column quietly evaluate to nothing.
 */
class ScriptRunner
{
    /**
     * Check if a string is valid base64 encoded
     */
    private static function isBase64Encoded($value)
    {
        if (empty($value)) {
            return false;
        }
        $decoded = base64_decode($value, true);
        return $decoded !== false && base64_encode($decoded) === $value;
    }

    /**
     * @param array  $parameters name => value, exposed to the script as globals
     * @param string $script     JavaScript source, plain or base64-encoded
     * @return string the script's completion value as a string, or '' on any failure
     */
    public static function executeJs($parameters, $script)
    {
        LogManager::getInstance()->debug(
            sprintf(
                'ScriptRunner: parameters:%s / script: %s',
                json_encode($parameters),
                $script
            )
        );

        // Callers pass the stored (base64) form or plain source interchangeably.
        $source = self::isBase64Encoded($script) ? base64_decode($script, true) : $script;
        if ($source === false || trim((string) $source) === '') {
            return '';
        }

        if (!is_array($parameters)) {
            $parameters = (array) $parameters;
        }

        try {
            $result = JsSandbox::evaluateToString($source, $parameters);
        } catch (JsError $e) {
            // A script author's mistake, or a budget breach. Expected, so log at a level
            // that will not page anyone, but never swallow it entirely.
            LogManager::getInstance()->info(
                sprintf('ScriptRunner: script error: %s | script: %s', $e->getMessage(), $source)
            );
            return '';
        } catch (\Throwable $e) {
            // A bug in the sandbox itself. Must not take the payroll run down, but it is
            // a real defect and is logged as one.
            LogManager::getInstance()->error(
                sprintf('ScriptRunner: sandbox failure: %s | script: %s', $e->getMessage(), $source)
            );
            return '';
        }

        LogManager::getInstance()->debug(
            sprintf(
                'ScriptRunner: result :%s',
                $result
            )
        );

        return $result;
    }
}
