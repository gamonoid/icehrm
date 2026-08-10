<?php

namespace Utils\Js;

/**
 * The public entry point for evaluating a sandboxed JavaScript expression.
 *
 * Replaces the previous runner, which base64'd the script onto a shell command line
 * and spawned `node core/execute/execute.js` to evaluate it inside vm2. That design
 * had three problems this one does not:
 *
 *   - vm2 was discontinued in 2023 with unfixable sandbox escapes; the pinned 3.9.11
 *     predates even its final patched release.
 *   - node was never installed in the production image, so exec() failed silently and
 *     every scripted payroll column computed as an empty string, with no error raised.
 *   - a process was spawned per employee per column (~39ms each), which dominated the
 *     runtime of a payroll run.
 *
 * Nothing here can reach outside itself: the interpreter has no host bindings, and the
 * script never becomes PHP. See JsInterpreter for the security model.
 *
 * Supported: function declarations and calls (NO recursion), + - * / %, comparison and
 * logical operators, ternary, if/else, for/while/do, arrays and array methods, strings
 * and string methods, object literals, Math.*, Number/String/Boolean, parseInt,
 * parseFloat, isNaN, isFinite, JSON.stringify.
 */
class JsSandbox
{
    /**
     * Evaluate a script and return its completion value — the value of the last
     * expression statement, which is the convention the payroll columns already use
     * (a trailing `Math.round(x * 100) / 100;` with no `return`).
     *
     * @param string $script    JavaScript source
     * @param array  $variables name => value, injected as globals
     * @return mixed a PHP scalar, JsArray, JsObject or JsUndefined
     * @throws JsError on a syntax error, an unsupported construct, or a budget breach
     */
    public static function evaluate($script, $variables = array())
    {
        $ast = JsParser::parseSource($script);
        $interpreter = new JsInterpreter();
        return $interpreter->run($ast, $variables);
    }

    /**
     * Check an author-supplied script and explain what is wrong with it.
     *
     * Use this wherever a human is editing a function (the payroll-column editor), and
     * evaluate() where the system is running one. See JsValidator for the checks.
     *
     * @param string $script
     * @param array  $variables name => sample value the column will supply
     * @return array the validation report
     */
    public static function validate($script, $variables = array())
    {
        return JsValidator::validate($script, $variables);
    }

    /**
     * Evaluate and stringify, matching what the old node runner wrote to stdout
     * (`process.stdout.write(`${vm.run(script)}`)`), so callers see byte-identical
     * results to the previous implementation.
     *
     * @return string
     * @throws JsError
     */
    public static function evaluateToString($script, $variables = array())
    {
        return JsRuntime::toString(self::evaluate($script, $variables));
    }
}
