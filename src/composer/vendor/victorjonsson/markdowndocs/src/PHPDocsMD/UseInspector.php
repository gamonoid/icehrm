<?php
namespace PHPDocsMD;

/**
 * Class that can extract all use statements in a file
 * @package PHPDocsMD
 */
class UseInspector
{

    /**
     * @param string $content
     * @return string[]
     */
    public function getUseStatementsInString($content)
    {
        $usages = [];
        $chunks = array_slice(preg_split('/use[\s+]/', $content), 1);
        foreach ($chunks as $chunk) {
                $usage = trim(current(explode(';', $chunk)));
                $usages[] = Utils::sanitizeClassName($usage);
        }

        return $usages;
    }

    /**
     * @param string $filePath
     * @return array
     */
    public function getUseStatementsInFile($filePath)
    {
        return $this->getUseStatementsInString(file_get_contents($filePath));
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return array
     */
    public function getUseStatements(\ReflectionClass $reflectionClass)
    {
        $classUseStatements = [];
        $classFile = $reflectionClass->getFileName();
        if ($classFile) {
            $classUseStatements = $this->getUseStatementsInFile($classFile);
        }
        return $classUseStatements;
    }

}