<?php
namespace PHPDocsMD;

/**
 * Class capable of creating ClassEntity objects
 * @package PHPDocsMD
 */
class ClassEntityFactory
{
    /**
     * @var DocInfoExtractor
     */
    private $docInfoExtractor;

    /**
     * @param DocInfoExtractor $docInfoExtractor
     */
    public function __construct(DocInfoExtractor $docInfoExtractor)
    {
        $this->docInfoExtractor = $docInfoExtractor;
    }

    public function create(\ReflectionClass $reflection)
    {
        $class = new ClassEntity();
        $docInfo = $this->docInfoExtractor->extractInfo($reflection);
        $this->docInfoExtractor->applyInfoToEntity($reflection, $docInfo, $class);
        $class->isInterface($reflection->isInterface());
        $class->isAbstract($reflection->isAbstract());
        $class->setInterfaces(array_keys($reflection->getInterfaces()));
        $class->hasIgnoreTag($docInfo->shouldBeIgnored());

        if ($reflection->getParentClass()) {
            $class->setExtends($reflection->getParentClass()->getName());
        }

        return $class;
    }

}