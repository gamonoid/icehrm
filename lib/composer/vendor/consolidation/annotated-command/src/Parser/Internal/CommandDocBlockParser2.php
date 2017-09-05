<?php
namespace Consolidation\AnnotatedCommand\Parser\Internal;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag\ParamTag;
use phpDocumentor\Reflection\DocBlock\Tag\ReturnTag;
use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Consolidation\AnnotatedCommand\Parser\DefaultsWithDescriptions;

/**
 * Given a class and method name, parse the annotations in the
 * DocBlock comment, and provide accessor methods for all of
 * the elements that are needed to create an annotated Command.
 */
class CommandDocBlockParser2 extends AbstractCommandDocBlockParser
{
    /**
     * Parse the docBlock comment for this command, and set the
     * fields of this class with the data thereby obtained.
     */
    public function parse()
    {
        $docblockComment = $this->reflection->getDocComment();
        $phpdoc = new DocBlock($docblockComment);

        // First set the description (synopsis) and help.
        $this->commandInfo->setDescription((string)$phpdoc->getShortDescription());
        $this->commandInfo->setHelp((string)$phpdoc->getLongDescription());

        $this->processAllTags($phpdoc);
    }

    protected function getTagContents($tag)
    {
        return $tag->getContent();
    }

    /**
     * Store the data from a @arg annotation in our argument descriptions.
     */
    protected function processArgumentTag($tag)
    {
        if (!$this->pregMatchNameAndDescription((string)$tag->getDescription(), $match)) {
            return;
        }
        $this->addOptionOrArgumentTag($tag, $this->commandInfo->arguments(), $match);
    }

    /**
     * Store the data from a @param annotation in our argument descriptions.
     */
    protected function processParamTag($tag)
    {
        if (!$tag instanceof ParamTag) {
            return;
        }
        return parent::processParamTag($tag);
    }

    /**
     * Store the data from a @return annotation in our argument descriptions.
     */
    protected function processReturnTag($tag)
    {
        if (!$tag instanceof ReturnTag) {
            return;
        }
        $this->commandInfo->setReturnType($tag->getType());
    }
}
