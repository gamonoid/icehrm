<?php
namespace Consolidation\TestUtils;

use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Consolidation\AnnotatedCommand\CommandInfoAltererInterface;

class ExampleCommandInfoAlterer implements CommandInfoAltererInterface
{
    public function alterCommandInfo(CommandInfo $commandInfo, $commandFileInstance)
    {
        if ($commandInfo->hasAnnotation('arbitrary')) {
            $commandInfo->addAnnotation('dynamic', "This annotation was dynamically added by ExampleCommandInfoAlterer");
        }
    }
}
