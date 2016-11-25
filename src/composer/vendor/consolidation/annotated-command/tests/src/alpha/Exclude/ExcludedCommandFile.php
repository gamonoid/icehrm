<?php
namespace Consolidation\TestUtils\alpha\Exclude;

/**
 * Test file used in the testCommandDiscovery() test.
 *
 * This commandfile is NOT found by the test.  It is in a searched
 * location (@see Consolidation\TestUtils\alpha\Exclude\IncludedCommandFile),
 * but it is in a folder named 'Exclude', which is excluded form search.
 */
class ExcludedCommandFile
{

}
