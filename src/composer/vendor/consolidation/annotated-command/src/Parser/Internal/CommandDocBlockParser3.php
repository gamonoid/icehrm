<?php
namespace Consolidation\AnnotatedCommand\Parser\Internal;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Consolidation\AnnotatedCommand\Parser\DefaultsWithDescriptions;

/**
 * Given a class and method name, parse the annotations in the
 * DocBlock comment, and provide accessor methods for all of
 * the elements that are needed to create an annotated Command.
 */
class CommandDocBlockParser3 extends AbstractCommandDocBlockParser
{
    /**
     * Parse the docBlock comment for this command, and set the
     * fields of this class with the data thereby obtained.
     */
    public function parse()
    {
        // DocBlockFactory::create fails if the comment is empty.
        $docComment = $this->reflection->getDocComment();
        if (empty($docComment)) {
            return;
        }
        $phpdoc = $this->createDocBlock();

        // First set the description (synopsis) and help.
        $this->commandInfo->setDescription((string)$phpdoc->getSummary());
        $this->commandInfo->setHelp((string)$phpdoc->getDescription());

        $this->processAllTags($phpdoc);
    }

    public function createDocBlock()
    {
        $docBlockFactory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $contextFactory = new \phpDocumentor\Reflection\Types\ContextFactory();

        return $docBlockFactory->create(
            $this->reflection,
            $contextFactory->createFromReflector($this->reflection)
        );
    }

    protected function getTagContents($tag)
    {
        return (string)$tag;
    }

    /**
     * Store the data from a @param annotation in our argument descriptions.
     */
    protected function processParamTag($tag)
    {
        if (!$tag instanceof Param) {
            return;
        }
        return parent::processParamTag($tag);
    }

    /**
     * Store the data from a @return annotation in our argument descriptions.
     */
    protected function processReturnTag($tag)
    {
        if (!$tag instanceof Return_) {
            return;
        }
        // If there is a spurrious trailing space on the return type, remove it.
        $this->commandInfo->setReturnType(trim($this->getTagContents($tag)));
    }
}
