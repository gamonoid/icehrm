<?php
namespace PHPDocsMD;

/**
 * @package PHPDocsMD
 */
class Utils
{

    /**
     * @param string $name
     * @return string
     */
    public static function sanitizeClassName($name)
    {
        return '\\'.trim($name, ' \\');
    }

    /**
     * @param string $fullClassName
     * @return string
     */
    public static function getClassBaseName($fullClassName)
    {
        $parts = explode('\\', trim($fullClassName));
        return end($parts);
    }

    /**
     * @param string $typeDeclaration
     * @param string $currentNameSpace
     * @param string $delimiter
     * @return string
     */
    public static function sanitizeDeclaration($typeDeclaration, $currentNameSpace, $delimiter='|')
    {
        $parts = explode($delimiter, $typeDeclaration);
        foreach($parts as $i=>$p) {
            if (self::shouldPrefixWithNamespace($p)) {
                $p = self::sanitizeClassName('\\' . trim($currentNameSpace, '\\') . '\\' . $p);
            } elseif (self::isClassReference($p)) {
                $p = self::sanitizeClassName($p);
            }
            $parts[$i] = $p;
        }
        return implode('/', $parts);
    }

    /**
     * @param string $typeDeclaration
     * @return bool
     */
    private static function shouldPrefixWithNameSpace($typeDeclaration)
    {
        return strpos($typeDeclaration, '\\') !== 0 && self::isClassReference($typeDeclaration);
    }

    /**
     * @param string $typeDeclaration
     * @return bool
     */
    public static function isClassReference($typeDeclaration)
    {
        $natives = [
            'mixed',
            'string',
            'int',
            'float',
            'integer',
            'number',
            'bool',
            'boolean',
            'object',
            'false',
            'true',
            'null',
            'array',
            'void',
            'callable'
        ];
        $sanitizedTypeDeclaration = rtrim(trim(strtolower($typeDeclaration)), '[]');

        return !in_array($sanitizedTypeDeclaration, $natives) &&
            strpos($typeDeclaration, ' ') === false;
    }

    public static function isNativeClassReference($typeDeclaration)
    {
        $sanitizedType = str_replace('[]', '', $typeDeclaration);
        if (Utils::isClassReference($typeDeclaration) && class_exists($sanitizedType, false)) {
            $reflectionClass = new \ReflectionClass($sanitizedType);
            return !$reflectionClass->getFileName();
        }
        return false;
    }
}