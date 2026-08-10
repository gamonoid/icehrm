<?php

namespace Utils\Js;

/**
 * Validates an author-supplied script and explains precisely what is wrong.
 *
 * This backs the "validate function" action in the payroll-column editor. The old
 * implementation ran the script through node/vm2 and reported only whether the result
 * was non-empty, so every failure — a typo, an unsupported construct, a missing
 * variable, a crashed node process — looked identical: "invalid". An author had no way
 * to tell a syntax error from a misspelled variable name.
 *
 * Four checks, in the order that produces the most useful first message:
 *
 *   1. PARSE. Syntax errors and unsupported constructs, reported with the line number
 *      and, for unsupported ones, what was used.
 *   2. FREE VARIABLES. Names the script reads that the column does not supply. This is
 *      the failure that is otherwise invisible: `gpssRate` instead of `gpssaRate` is
 *      valid JavaScript that quietly yields 0 through the `|| 0` idiom, and the column
 *      computes a wrong number with no error anywhere.
 *   3. DRY RUN. Execute with sample values to catch what only appears at runtime —
 *      calling a non-function, reading a property of undefined, recursion, or a budget.
 *   4. RESULT SANITY. A script that evaluates to NaN/Infinity or to nothing is
 *      reported, since a payroll column is expected to produce a number.
 */
class JsValidator
{
    /**
     * @param string $script     the author's source
     * @param array  $variables  name => sample value, as the column will supply them
     * @return array {
     *     valid: bool,               everything passed
     *     error: string|null,        first problem, ready to show the author
     *     line: int|null,            1-based line in the script, when known
     *     kind: string|null,         JsError::KIND_* , or 'undefined-variable'
     *     undefinedVariables: array, names read but not supplied
     *     functions: array,          function names the script declares
     *     result: string|null,       the dry-run result
     *     isNumeric: bool            the dry run produced a usable number
     * }
     */
    public static function validate($script, $variables = array())
    {
        $report = array(
            'valid' => false,
            'error' => null,
            'line' => null,
            'kind' => null,
            'undefinedVariables' => array(),
            'functions' => array(),
            'result' => null,
            'isNumeric' => false,
        );

        $source = (string) $script;
        if (trim($source) === '') {
            $report['error'] = 'The function is empty.';
            $report['kind'] = JsError::KIND_SYNTAX;
            return $report;
        }

        // 1. Parse.
        try {
            $ast = JsParser::parseSource($source);
        } catch (JsError $e) {
            $report['error'] = self::describe($e);
            $report['line'] = $e->getScriptLine();
            $report['kind'] = $e->getKind();
            return $report;
        }

        // 2. Free variables.
        $analysis = JsAnalyzer::analyze($ast);
        $report['functions'] = $analysis['functions'];
        $supplied = array_keys($variables);
        $missing = array();
        foreach ($analysis['free'] as $name) {
            if (!in_array($name, $supplied, true)) {
                $missing[] = $name;
            }
        }
        $report['undefinedVariables'] = $missing;

        // 3. Dry run. Missing names are bound to 0 so execution can proceed and reveal
        //    any further problem, rather than stopping at the first unknown name.
        $runVariables = $variables;
        foreach ($missing as $name) {
            $runVariables[$name] = 0;
        }

        try {
            $interpreter = new JsInterpreter();
            $value = $interpreter->run($ast, $runVariables);
        } catch (JsError $e) {
            $report['error'] = self::describe($e);
            $report['line'] = $e->getScriptLine();
            $report['kind'] = $e->getKind();
            return $report;
        } catch (\Throwable $e) {
            $report['error'] = 'The function could not be evaluated.';
            $report['kind'] = JsError::KIND_RUNTIME;
            return $report;
        }

        $report['result'] = JsRuntime::toString($value);

        // A missing variable is reported only now, so the author sees it together with
        // whatever the script produced without it.
        if (!empty($missing)) {
            $report['kind'] = 'undefined-variable';
            $report['error'] = self::describeMissing($missing, $supplied);
            return $report;
        }

        // 4. Result sanity.
        //
        // NaN and Infinity are only meaningful for a value that IS a number. Running
        // ToNumber on everything would report a perfectly good text result — a payslip
        // label, say — as "produced NaN", which is both wrong and baffling.
        $isNumber = is_float($value) || is_int($value);
        $number = $isNumber ? (float) $value : null;
        $report['isNumeric'] = $isNumber && !is_nan($number) && !is_infinite($number);

        if (JsRuntime::isUndefined($value)) {
            $report['error'] = 'The function did not produce a value. End it with the '
                . 'expression to return, for example: Math.round(total * 100) / 100;';
            $report['kind'] = JsError::KIND_RUNTIME;
            return $report;
        }
        if ($isNumber && is_nan($number)) {
            $report['error'] = 'The function produced NaN (not a number). This usually '
                . 'means a value was used in arithmetic before being converted, for '
                . 'example Number(x) on a non-numeric value.';
            $report['kind'] = JsError::KIND_RUNTIME;
            return $report;
        }
        if ($isNumber && is_infinite($number)) {
            $report['error'] = 'The function produced Infinity, which usually means a '
                . 'division by zero.';
            $report['kind'] = JsError::KIND_RUNTIME;
            return $report;
        }

        $report['valid'] = true;
        return $report;
    }

    /** A one-line message with the position folded in, ready to show as-is. */
    private static function describe($error)
    {
        $message = $error->getMessage();
        $line = $error->getScriptLine();
        if ($line !== null && $line > 0) {
            $message .= ' (line ' . $line . ')';
        }
        if ($error->getKind() === JsError::KIND_UNSUPPORTED) {
            $message .= '. This sandbox runs a restricted subset of JavaScript: '
                . 'functions, arithmetic, Math, strings and arrays.';
        }
        return $message;
    }

    private static function describeMissing($missing, $supplied)
    {
        $parts = array();
        foreach ($missing as $name) {
            $suggestion = self::closest($name, $supplied);
            $parts[] = $suggestion === null ? '"' . $name . '"'
                : '"' . $name . '" (did you mean "' . $suggestion . '"?)';
        }

        $noun = count($missing) === 1 ? 'variable' : 'variables';
        $message = 'Unknown ' . $noun . ' not available to this function: '
            . implode(', ', $parts) . '.';
        if (!empty($supplied)) {
            $message .= ' Available: ' . implode(', ', $supplied) . '.';
        }
        return $message;
    }

    /** Nearest supplied name within a small edit distance, for a "did you mean". */
    private static function closest($name, $candidates)
    {
        $best = null;
        $bestDistance = PHP_INT_MAX;
        foreach ($candidates as $candidate) {
            // levenshtein() is byte-based and capped at 255 chars; variable names are
            // far shorter, so no guard is needed beyond skipping absurd inputs.
            if (strlen($candidate) > 255 || strlen($name) > 255) {
                continue;
            }
            $distance = levenshtein(strtolower($name), strtolower($candidate));
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $best = $candidate;
            }
        }
        // Only suggest when the names are genuinely close, otherwise the hint misleads.
        $threshold = max(1, (int) floor(strlen($name) / 3));
        return $bestDistance <= $threshold ? $best : null;
    }
}
