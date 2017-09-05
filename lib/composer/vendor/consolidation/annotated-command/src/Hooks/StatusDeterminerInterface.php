<?php
namespace Consolidation\AnnotatedCommand\Hooks;

/**
 * A StatusDeterminer maps from a result to a status exit code.
 *
 * @see HookManager::addStatusDeterminer()
 */
interface StatusDeterminerInterface
{
    /**
     * Convert a result object into a status code, if
     * possible. Return null if the result object is unknown.
     *
     * @return null|integer
     */
    public function determineStatusCode($result);
}
