<?php
namespace PHPDocsMD;

/**
 * Class that can extract information from a function/class comment
 * @package PHPDocsMD
 */
class DocInfoExtractor
{
    /**
     * @param \ReflectionClass|\ReflectionMethod $reflection
     * @return DocInfo
     */
    public function extractInfo($reflection)
    {
        $comment = $this->getCleanDocComment($reflection);
        $data = $this->extractInfoFromComment($comment, $reflection);
        return new DocInfo($data);
    }

    /**
     * @param \ReflectionClass|\ReflectionMethod $reflection
     * @param DocInfo $docInfo
     * @param CodeEntity $code
     */
    public function applyInfoToEntity($reflection, DocInfo $docInfo, CodeEntity $code)
    {
        $code->setName($reflection->getName());
        $code->setDescription($docInfo->getDescription());
        $code->setExample($docInfo->getExample());

        if ($docInfo->getDeprecationMessage()) {
            $code->isDeprecated(true);
            $code->setDeprecationMessage($docInfo->getDeprecationMessage());
        }
    }

    /**
     * @param \ReflectionClass $reflection
     * @return string
     */
    private function getCleanDocComment($reflection)
    {
        $comment = str_replace(['/*', '*/'], '', $reflection->getDocComment());
        return trim(trim(preg_replace('/([\s|^]\*\s)/', '', $comment)), '*');
    }

    /**
     * @param string $comment
     * @param string $current_tag
     * @param \ReflectionMethod|\ReflectionClass $reflection
     * @return array
     */
    private function extractInfoFromComment($comment, $reflection, $current_tag='description')
    {
        $currentNamespace = $this->getNameSpace($reflection);
        $tags = [$current_tag=>''];

        foreach(explode(PHP_EOL, $comment) as $line) {

            if( $current_tag != 'example' )
                $line = trim($line);

            $words = $this->getWordsFromLine($line);
            if( empty($words) )
                continue;

            if( strpos($words[0], '@') === false ) {
                // Append to tag
                $joinWith = $current_tag == 'example' ? PHP_EOL : ' ';
                $tags[$current_tag] .= $joinWith . $line;
            }
            elseif( $words[0] == '@param' ) {
                // Get parameter declaration
                if( $paramData = $this->figureOutParamDeclaration($words, $currentNamespace) ) {
                    list($name, $data) = $paramData;
                    $tags['params'][$name] = $data;
                }
            }
            else {
                // Start new tag
                $current_tag = substr($words[0], 1);
                array_splice($words, 0 ,1);
                if( empty($tags[$current_tag]) ) {
                    $tags[$current_tag] = '';
                }
                $tags[$current_tag] .= trim(join(' ', $words));
            }
        }

        foreach($tags as $name => $val) {
            if( is_array($val) ) {
                foreach($val as $subName=>$subVal) {
                    if( is_string($subVal) )
                        $tags[$name][$subName] = trim($subVal);
                }
            } else {
                $tags[$name] = trim($val);
            }
        }

        return $tags;
    }

    /**
     * @param \ReflectionClass|\ReflectionMethod $reflection
     * @return string
     */
    private function getNameSpace($reflection)
    {
        if ($reflection instanceof \ReflectionClass) {
            return $reflection->getNamespaceName();
        } else {
            return $reflection->getDeclaringClass()->getNamespaceName();
        }
    }

    /**
     * @param $line
     * @return array
     */
    private function getWordsFromLine($line)
    {
        $words = [];
        foreach(explode(' ', trim($line)) as $w) {
            if( !empty($w) ) {
                $words[] = $w;
            }
        }
        return $words;
    }

    /**
     * @param $words
     * @param $currentNameSpace
     * @return array|bool
     */
    private function figureOutParamDeclaration($words, $currentNameSpace)
    {
        $description = '';
        $type = '';
        $name = '';

        if (isset($words[1]) && strpos($words[1], '$') === 0) {
            $name = $words[1];
            $type = 'mixed';
            array_splice($words, 0, 2);
        } elseif (isset($words[2])) {
            $name = $words[2];
            $type = $words[1];
            array_splice($words, 0, 3);
        }

        if (!empty($name)) {
            $name = current(explode('=', $name));
            if( count($words) > 1 ) {
                $description = join(' ', $words);
            }

            $type = Utils::sanitizeDeclaration($type, $currentNameSpace);

            $data = [
                'description' => $description,
                'name' => $name,
                'type' => $type,
                'default' => false
            ];

            return [$name, $data];
        }

        return false;
    }
}
