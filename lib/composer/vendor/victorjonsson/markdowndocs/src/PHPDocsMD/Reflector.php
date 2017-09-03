<?php
namespace PHPDocsMD;

use ReflectionMethod;


/**
 * Class that can compute ClassEntity objects out of real classes
 * @package PHPDocsMD
 */
class Reflector implements ReflectorInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var FunctionFinder
     */
    private $functionFinder;

    /**
     * @var DocInfoExtractor
     */
    private $docInfoExtractor;
    /**
     * @var ClassEntityFactory
     */
    private $classEntityFactory;
    /**
     * @var UseInspector
     */
    private $useInspector;

    /**
     * @param string $className
     * @param FunctionFinder $functionFinder
     * @param DocInfoExtractor $docInfoExtractor
     * @param UseInspector $useInspector
     * @param ClassEntityFactory $classEntityFactory
     */
    function __construct(
        $className,
        FunctionFinder $functionFinder = null,
        DocInfoExtractor $docInfoExtractor = null,
        UseInspector $useInspector = null,
        ClassEntityFactory $classEntityFactory = null
    ) {
        $this->className = $className;
        $this->functionFinder = $this->loadIfNull($functionFinder, FunctionFinder::class);
        $this->docInfoExtractor = $this->loadIfNull($docInfoExtractor, DocInfoExtractor::class);
        $this->useInspector = $this->loadIfNull($useInspector, UseInspector::class);
        $this->classEntityFactory = $this->loadIfNull(
            $classEntityFactory,
            ClassEntityFactory::class,
            $this->docInfoExtractor
        );
    }
    
    private function loadIfNull($obj, $className, $in=null)
    {
        return is_object($obj) ? $obj : new $className($in);
    }
    
    /**
     * @return \PHPDocsMD\ClassEntity
     */
    function getClassEntity() {
        $classReflection = new \ReflectionClass($this->className);
        $classEntity = $this->classEntityFactory->create($classReflection);

        $classEntity->setFunctions($this->getClassFunctions($classEntity, $classReflection));

        return $classEntity;
    }

    /**
     * @param ClassEntity $classEntity
     * @param \ReflectionClass $reflectionClass
     * @return FunctionEntity[]
     */
    private function getClassFunctions(ClassEntity $classEntity, \ReflectionClass $reflectionClass)
    {
        $classUseStatements = $this->useInspector->getUseStatements($reflectionClass);
        $publicFunctions = [];
        $protectedFunctions = [];

        foreach($reflectionClass->getMethods() as $methodReflection) {

            $func = $this->createFunctionEntity(
                $methodReflection,
                $classEntity,
                $classUseStatements
            );


            if( $func ) {
                if( $func->getVisibility() == 'public' ) {
                    $publicFunctions[$func->getName()] =  $func;
                } else {
                    $protectedFunctions[$func->getName()] = $func;
                }
            }
        }

        ksort($publicFunctions);
        ksort($protectedFunctions);

        return array_values(array_merge($publicFunctions, $protectedFunctions));
    }

    /**
     * @param ReflectionMethod $method
     * @param ClassEntity $class
     * @param array $useStatements
     * @return bool|FunctionEntity
     */
    protected function createFunctionEntity(ReflectionMethod $method, ClassEntity $class, $useStatements)
    {
        $func = new FunctionEntity();
        $docInfo = $this->docInfoExtractor->extractInfo($method);
        $this->docInfoExtractor->applyInfoToEntity($method, $docInfo, $func);

        if ($docInfo->shouldInheritDoc()) {
            return $this->findInheritedFunctionDeclaration($func, $class);
        }

        if ($this->shouldIgnoreFunction($docInfo, $method, $class)) {
            return false;
        }

        $returnType = $this->getReturnType($docInfo, $method, $func, $useStatements);
        $func->setReturnType($returnType);
        $func->setParams($this->getParams($method, $docInfo));
        $func->isStatic($method->isStatic());
        $func->setVisibility($method->isPublic() ? 'public' : 'protected');
        $func->isAbstract($method->isAbstract());
        $func->setClass($class->getName());
        $func->isReturningNativeClass(Utils::isNativeClassReference($returnType));

        return $func;
    }

    /**
     * @param DocInfo $docInfo
     * @param ReflectionMethod $method
     * @param FunctionEntity $func
     * @param array $useStatements
     * @return string
     */
    private function getReturnType(
        DocInfo $docInfo,
        ReflectionMethod $method,
        FunctionEntity $func,
        array $useStatements
    ) {
        $returnType = $docInfo->getReturnType();
        if (empty($returnType)) {
            $returnType = $this->guessReturnTypeFromFuncName($func->getName());
        } elseif(Utils::isClassReference($returnType) && !self::classExists($returnType)) {
            $isReferenceToArrayOfObjects = substr($returnType, -2) == '[]' ? '[]':'';
            if ($isReferenceToArrayOfObjects) {
                $returnType = substr($returnType, 0, strlen($returnType)-2);
            }
            $className = $this->stripAwayNamespace($returnType);
            foreach ($useStatements as $usedClass) {
                if ($this->stripAwayNamespace($usedClass) == $className) {
                    $returnType = $usedClass;
                    break;
                }
            }
            if ($isReferenceToArrayOfObjects) {
                $returnType .= '[]';
            }
        }

        return Utils::sanitizeDeclaration(
            $returnType,
            $method->getDeclaringClass()->getNamespaceName()
        );
    }

    /**
     * @param string $classRef
     * @return bool
     */
    private function classExists($classRef)
    {
        return class_exists(trim($classRef, '[]'));
    }

    /**
     * @param string $className
     * @return string
     */
    private function stripAwayNamespace($className)
    {
        return trim(substr($className, strrpos($className, '\\')), '\\');
    }

    /**
     * @param DocInfo $info
     * @param ReflectionMethod $method
     * @param ClassEntity $class
     * @return bool
     */
    protected function shouldIgnoreFunction($info, ReflectionMethod $method, $class)
    {
        return $info->shouldBeIgnored() ||
                $method->isPrivate() ||
                !$class->isSame($method->getDeclaringClass()->getName());
    }

    /**
     * @todo Turn this into a class "FunctionEntityFactory"
     * @param \ReflectionParameter $reflection
     * @param array $docs
     * @return FunctionEntity
     */
    private function createParameterEntity(\ReflectionParameter $reflection, $docs)
    {
        // need to use slash instead of pipe or md-generation will get it wrong
        $def = false;
        $type = 'mixed';
        $declaredType = self::getParamType($reflection);
        if( !isset($docs['type']) )
            $docs['type'] = '';

        if( $declaredType && !($declaredType=='array' && substr($docs['type'], -2) == '[]') && $declaredType != $docs['type']) {
            if( $declaredType && $docs['type'] ) {
                $posClassA = Utils::getClassBaseName($docs['type']);
                $posClassB = Utils::getClassBaseName($declaredType);
                if( $posClassA == $posClassB ) {
                    $docs['type'] = $declaredType;
                } else {
                    $docs['type'] = empty($docs['type']) ? $declaredType : $docs['type'].'/'.$declaredType;
                }
            } else {
                $docs['type'] = empty($docs['type']) ? $declaredType : $docs['type'].'/'.$declaredType;
            }
        }

        try {
            $def = $reflection->getDefaultValue();
            $type = $this->getTypeFromVal($def);
            if( is_string($def) ) {
                $def = "`'$def'`";
            } elseif( is_bool($def) ) {
                $def = $def ? 'true':'false';
            } elseif( is_null($def) ) {
                $def = 'null';
            } elseif( is_array($def) ) {
                $def = 'array()';
            }
        } catch(\Exception $e) {}

        $varName = '$'.$reflection->getName();

        if( !empty($docs) ) {
            $docs['default'] = $def;
            if( $type == 'mixed' && $def == 'null' && strpos($docs['type'], '\\') === 0 ) {
                $type = false;
            }
            if( $type && $def && !empty($docs['type']) && $docs['type'] != $type && strpos($docs['type'], '|') === false) {
                if( substr($docs['type'], strpos($docs['type'], '\\')) == substr($declaredType, strpos($declaredType, '\\')) ) {
                    $docs['type'] = $declaredType;
                } else {
                    $docs['type'] = ($type == 'mixed' ? '':$type.'/').$docs['type'];
                }
            } elseif( $type && empty($docs['type']) ) {
                $docs['type'] = $type;
            }
        } else {
            $docs = [
                'descriptions'=>'',
                'name' => $varName,
                'default' => $def,
                'type' => $type
            ];
        }

        $param = new ParamEntity();
        $param->setDescription(isset($docs['description']) ? $docs['description']:'');
        $param->setName($varName);
        $param->setDefault($docs['default']);
        $param->setType(empty($docs['type']) ? 'mixed':str_replace(['|', '\\\\'], ['/', '\\'], $docs['type']));
        return $param;
    }

    /**
     * Tries to find out if the type of the given parameter. Will
     * return empty string if not possible.
     *
     * @example
     * <code>
     *  <?php
     *      $reflector = new \\ReflectionClass('MyClass');
     *      foreach($reflector->getMethods() as $method ) {
     *          foreach($method->getParameters() as $param) {
     *              $name = $param->getName();
     *              $type = Reflector::getParamType($param);
     *              printf("%s = %s\n", $name, $type);
     *          }
     *      }
     * </code>
     *
     * @param \ReflectionParameter $refParam
     * @return string
     */
    static function getParamType(\ReflectionParameter $refParam)
    {
        $export = \ReflectionParameter::export([
                $refParam->getDeclaringClass()->name,
                $refParam->getDeclaringFunction()->name
            ],
            $refParam->name,
            true
        );

        $export =  str_replace(' or NULL', '', $export);

        $type = preg_replace('/.*?([\w\\\]+)\s+\$'.current(explode('=', $refParam->name)).'.*/', '\\1', $export);
        if( strpos($type, 'Parameter ') !== false ) {
            return '';
        }

        if( $type != 'array' && strpos($type, '\\') !== 0 ) {
            $type = '\\'.$type;
        }

        return $type;
    }

    /**
     * @param string $name
     * @return string
     */
    private function guessReturnTypeFromFuncName($name)
    {
        $mixed = ['get', 'load', 'fetch', 'find', 'create'];
        $bool = ['is', 'can', 'has', 'have', 'should'];
        foreach($mixed as $prefix) {
            if( strpos($name, $prefix) === 0 )
                return 'mixed';
        }
        foreach($bool as $prefix) {
            if( strpos($name, $prefix) === 0 )
                return 'bool';
        }
        return 'void';
    }

    /**
     * @param string $def
     * @return string
     */
    private function getTypeFromVal($def)
    {
        if( is_string($def) ) {
            return 'string';
        } elseif( is_bool($def) ) {
            return 'bool';
        } elseif( is_array($def) ) {
            return 'array';
        } else {
            return 'mixed';
        }
    }

    /**
     * @param FunctionEntity $func
     * @param ClassEntity $class
     * @return FunctionEntity
     */
    private function findInheritedFunctionDeclaration(FunctionEntity $func, ClassEntity $class)
    {
        $funcName = $func->getName();
        $inheritedFuncDeclaration = $this->functionFinder->find(
            $funcName,
            $class->getExtends()
        );
        if (!$inheritedFuncDeclaration) {
            $inheritedFuncDeclaration = $this->functionFinder->findInClasses(
                $funcName,
                $class->getInterfaces()
            );
            if (!$inheritedFuncDeclaration) {
                throw new \RuntimeException(
                    'Function '.$funcName.' tries to inherit docs but no parent method is found'
                );
            }
        }
        if (!$func->isAbstract() && !$class->isAbstract() && $inheritedFuncDeclaration->isAbstract()) {
            $inheritedFuncDeclaration->isAbstract(false);
        }
        return $inheritedFuncDeclaration;
    }

    /**
     * @param ReflectionMethod $method
     * @param DocInfo $docInfo
     * @return array
     */
    private function getParams(ReflectionMethod $method, $docInfo)
    {
        $params = [];
        foreach ($method->getParameters() as $param) {
            $paramName = '$' . $param->getName();
            $params[$param->getName()] = $this->createParameterEntity(
                $param,
                $docInfo->getParameterInfo($paramName)
            );
        }
        return array_values($params);
    }
}
