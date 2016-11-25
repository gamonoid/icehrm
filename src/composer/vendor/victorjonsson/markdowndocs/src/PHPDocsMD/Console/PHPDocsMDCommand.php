<?php
namespace PHPDocsMD\Console;

use PHPDocsMD\MDTableGenerator;
use PHPDocsMD\Reflector;

use PHPDocsMD\Utils;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Console command used to extract markdown-formatted documentation from classes
 * @package PHPDocsMD\Console
 */
class PHPDocsMDCommand extends \Symfony\Component\Console\Command\Command {

    const ARG_CLASS = 'class';
    const OPT_BOOTSTRAP = 'bootstrap';
    const OPT_IGNORE = 'ignore';

    /**
     * @var array
     */
    private $memory = [];

    /**
     * @param $name
     * @return \PHPDocsMD\ClassEntity
     */
    private function getClassEntity($name) {
        if( !isset($this->memory[$name]) ) {
            $reflector = new Reflector($name);
            $this->memory[$name] = $reflector->getClassEntity();
        }
        return $this->memory[$name];
    }

    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Get docs for given class/source directory)')
            ->addArgument(
                self::ARG_CLASS,
                InputArgument::REQUIRED,
                'Class or source directory'
            )
            ->addOption(
                self::OPT_BOOTSTRAP,
                'b',
                InputOption::VALUE_REQUIRED,
                'File to be included before generating documentation'
            )
            ->addOption(
                self::OPT_IGNORE,
                'i',
                InputOption::VALUE_REQUIRED,
                'Directories to ignore',
                ''
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $classes = $input->getArgument(self::ARG_CLASS);
        $bootstrap = $input->getOption(self::OPT_BOOTSTRAP);
        $ignore = explode(',', $input->getOption(self::OPT_IGNORE));
        $requestingOneClass = false;

        if( $bootstrap ) {
            require_once strpos($bootstrap,'/') === 0 ? $bootstrap : getcwd().'/'.$bootstrap;
        }

        $classCollection = [];
        if( strpos($classes, ',') !== false ) {
            foreach(explode(',', $classes) as $class) {
                if( class_exists($class) || interface_exists($class) )
                    $classCollection[0][] = $class;
            }
        }
        elseif( class_exists($classes) || interface_exists($classes) ) {
            $classCollection[] = array($classes);
            $requestingOneClass = true;
        } elseif( is_dir($classes) ) {
            $classCollection = $this->findClassesInDir($classes, [], $ignore);
        } else {
            throw new \InvalidArgumentException('Given input is neither a class nor a source directory');
        }

        $tableGenerator = new MDTableGenerator();
        $tableOfContent = [];
        $body = [];
        $classLinks = [];

        foreach($classCollection as $ns => $classes) {
            foreach($classes as $className) {
                $class = $this->getClassEntity($className);

                if( $class->hasIgnoreTag() )
                    continue;

                // Add to tbl of contents
                $tableOfContent[] = sprintf('- [%s](#%s)', $class->generateTitle('%name% %extra%'), $class->generateAnchor());

                $classLinks[$class->getName()] = '#'.$class->generateAnchor();

                // generate function table
                $tableGenerator->openTable();
                $tableGenerator->doDeclareAbstraction(!$class->isInterface());
                foreach($class->getFunctions() as $func) {
                    if ($func->isReturningNativeClass()) {
                        $classLinks[$func->getReturnType()] = 'http://php.net/manual/en/class.'.
                            strtolower(str_replace(array('[]', '\\'), '', $func->getReturnType())).
                            '.php';
                    }
                    foreach($func->getParams() as $param) {
                        if ($param->getNativeClassType()) {
                            $classLinks[$param->getNativeClassType()] = 'http://php.net/manual/en/class.'.
                                strtolower(str_replace(array('[]', '\\'), '', $param->getNativeClassType())).
                                '.php';
                        }
                    }
                    $tableGenerator->addFunc($func);
                }

                $docs = ($requestingOneClass ? '':'<hr /> ').PHP_EOL;

                if( $class->isDeprecated() ) {
                    $docs .= '### <strike>'.$class->generateTitle().'</strike>'.PHP_EOL.PHP_EOL.
                            '> **DEPRECATED** '.$class->getDeprecationMessage().PHP_EOL.PHP_EOL;
                }
                else {
                    $docs .= '### '.$class->generateTitle().PHP_EOL.PHP_EOL;
                    if( $class->getDescription() )
                        $docs .= '> '.$class->getDescription().PHP_EOL.PHP_EOL;
                }

                if( $example = $class->getExample() ) {
                    $docs .= '###### Example' . PHP_EOL . MDTableGenerator::formatExampleComment($example) .PHP_EOL.PHP_EOL;
                }

                $docs .= $tableGenerator->getTable().PHP_EOL;

                if( $class->getExtends() ) {
                    $link = $class->getExtends();
                    if( $anchor = $this->getAnchorFromClassCollection($classCollection, $class->getExtends()) ) {
                        $link = sprintf('[%s](#%s)', $link, $anchor);
                    }

                    $docs .= PHP_EOL.'*This class extends '.$link.'*'.PHP_EOL;
                }

                if( $interfaces = $class->getInterfaces() ) {
                    $interfaceNames = [];
                    foreach($interfaces as $interface) {
                        $anchor = $this->getAnchorFromClassCollection($classCollection, $interface);
                        $interfaceNames[] = $anchor ? sprintf('[%s](#%s)', $interface, $anchor) : $interface;
                    }
                    $docs .= PHP_EOL.'*This class implements '.implode(', ', $interfaceNames).'*'.PHP_EOL;
                }

                $body[] = $docs;
            }
        }

        if( empty($tableOfContent) ) {
            throw new \InvalidArgumentException('No classes found');
        } elseif( !$requestingOneClass ) {
            $output->writeln('## Table of contents'.PHP_EOL);
            $output->writeln(implode(PHP_EOL, $tableOfContent));
        }

        // Convert references to classes into links
        asort($classLinks);
        $classLinks = array_reverse($classLinks, true);
        $docString = implode(PHP_EOL, $body);
        foreach($classLinks as $className => $url) {
            $link = sprintf('[%s](%s)', $className, $url);
            $find = array('<em>'.$className, '/'.$className);
            $replace = array('<em>'.$link, '/'.$link);
            $docString = str_replace($find, $replace, $docString);
        }

        $output->writeln(PHP_EOL.$docString);
    }

    /**
     * @param $coll
     * @param $find
     * @return bool|string
     */
    private function getAnchorFromClassCollection($coll, $find)
    {
        foreach($coll as $ns => $classes) {
            foreach($classes as $className) {
                if( $className == $find ) {
                    return $this->getClassEntity($className)->generateAnchor();
                }
            }
        }
        return false;
    }

    /**
     * @param $file
     * @return array
     */
    private function findClassInFile($file)
    {
        $ns = '';
        $class = false;
        foreach(explode(PHP_EOL, file_get_contents($file)) as $line) {
            if ( strpos($line, '*') === false ) {
                if( strpos($line, 'namespace') !== false ) {
                    $ns = trim(current(array_slice(explode('namespace', $line), 1)), '; ');
                    $ns = Utils::sanitizeClassName($ns);
                } elseif( strpos($line, 'class') !== false ) {
                    $class = $this->extractClassNameFromLine('class', $line);
                    break;
                } elseif( strpos($line, 'interface') !== false ) {
                    $class = $this->extractClassNameFromLine('interface', $line);
                    break;
                }
            }
        }
        return $class ? array($ns, $ns .'\\'. $class) : array(false, false);
    }

    /**
     * @param string $type
     * @param string $line
     * @return string
     */
    function extractClassNameFromLine($type, $line)
    {
        $class = trim(current(array_slice(explode($type, $line), 1)), '; ');
        return trim(current(explode(' ', $class)));
    }

    /**
     * @param $dir
     * @param array $collection
     * @param array $ignores
     * @return array
     */
    private function findClassesInDir($dir, $collection=[], $ignores=[])
    {
        foreach(new \FilesystemIterator($dir) as $f) {
            /** @var \SplFileInfo $f */
            if( $f->isFile() && !$f->isLink() ) {
                list($ns, $className) = $this->findClassInFile($f->getRealPath());
                if( $className && (class_exists($className, true) || interface_exists($className)) ) {
                    $collection[$ns][] = $className;
                }
            } elseif( $f->isDir() && !$f->isLink() && !$this->shouldIgnoreDirectory($f->getFilename(), $ignores) ) {
                $collection = $this->findClassesInDir($f->getRealPath(), $collection);
            }
        }
        ksort($collection);
        return $collection;
    }

    /**
     * @param $dirName
     * @param $ignores
     * @return bool
     */
    private function shouldIgnoreDirectory($dirName, $ignores) {
        foreach($ignores as $dir) {
            $dir = trim($dir);
            if( !empty($dir) && substr($dirName, -1 * strlen($dir)) == $dir ) {
                return true;
            }
        }
        return false;
    }

}