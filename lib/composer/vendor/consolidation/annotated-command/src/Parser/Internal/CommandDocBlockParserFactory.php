<?php
namespace Consolidation\AnnotatedCommand\Parser\Internal;

use Consolidation\AnnotatedCommand\Parser\CommandInfo;

/**
 * Create an appropriate CommandDocBlockParser.
 */
class CommandDocBlockParserFactory
{
    public static function parse(CommandInfo $commandInfo, \ReflectionMethod $reflection)
    {
        return static::create($commandInfo, $reflection)->parse();
    }

    private static function create(CommandInfo $commandInfo, \ReflectionMethod $reflection)
    {
        if (static::hasReflectionDocBlock3()) {
            return new CommandDocBlockParser3($commandInfo, $reflection);
        }
        return new CommandDocBlockParser2($commandInfo, $reflection);
    }

    private static function hasReflectionDocBlock3()
    {
        return class_exists('phpDocumentor\Reflection\DocBlockFactory') && class_exists('phpDocumentor\Reflection\Types\ContextFactory');
    }
}
