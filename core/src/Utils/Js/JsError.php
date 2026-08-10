<?php

namespace Utils\Js;

/**
 * Every failure inside the JavaScript sandbox — a syntax error, an unsupported
 * construct, a runtime error, or a resource budget being exhausted.
 *
 * One exception type, but it carries the two things a script author actually needs:
 * WHERE (the source line) and WHAT KIND of problem it is. The payroll column editor
 * surfaces both, so "Unexpected \"}\" on line 4" beats a bare "invalid script".
 *
 * Deliberately not a hierarchy: callers switch on getKind() when they care and ignore
 * it otherwise, and the sandbox must never leak a PHP exception (whose message could
 * carry a file path or internal class name) out to a script author.
 */
class JsError extends \Exception
{
    /** The script is not valid JavaScript, or not valid in this subset. */
    const KIND_SYNTAX = 'syntax';
    /** Valid JavaScript, but a construct this sandbox intentionally does not run. */
    const KIND_UNSUPPORTED = 'unsupported';
    /** Parsed and started, but failed while executing (bad call, bad property...). */
    const KIND_RUNTIME = 'runtime';
    /** Hit a step, time, depth or size limit — includes the no-recursion rule. */
    const KIND_LIMIT = 'limit';

    private $scriptLine;
    private $kind;

    /**
     * @param string   $message human-readable, safe to show a script author
     * @param int|null $scriptLine 1-based line in the script, when known
     * @param string   $kind one of the KIND_* constants
     */
    public function __construct($message, $scriptLine = null, $kind = self::KIND_RUNTIME)
    {
        parent::__construct($message);
        $this->scriptLine = $scriptLine;
        $this->kind = $kind;
    }

    /**
     * The line in the SCRIPT (not getLine(), which is final on Exception and reports
     * the PHP file that threw).
     *
     * @return int|null
     */
    public function getScriptLine()
    {
        return $this->scriptLine;
    }

    /** @return string one of the KIND_* constants */
    public function getKind()
    {
        return $this->kind;
    }
}
