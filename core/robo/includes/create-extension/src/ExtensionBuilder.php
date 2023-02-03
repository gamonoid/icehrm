<?php

namespace CreateExtension;

class ExtensionBuilder
{
    protected $extensionName;
    protected $extensionsRoot;
    protected $type;
    
    public function __construct(string $extensionName, string $extensionsRoot, string $type)
    {
        $this->extensionName = $extensionName;
        $this->extensionsRoot = $extensionsRoot;
        $this->type = $type;
    }
    public function build( \RoboFile $robo) {
        $formatter = new CodeFormatter();
        if (is_dir($this->extensionsRoot.'/'.$this->extensionName.'/'.$this->type)
            && is_file($this->extensionsRoot.'/'.$this->extensionName.'/'.$this->type)
        ) {
            $robo->sayIt('Extension exists');
            throw new \Exception('Extension exists');
        }

        $result = mkdir($this->extensionsRoot.'/'.$this->extensionName.'/'.$this->type, 0755, true);
        if (!$result) {
            throw new \Exception('Could not create extension root directory :'.$this->extensionsRoot.'/'.$this->extensionName.'/'.$this->type);
        }

        $extRoot = $this->extensionsRoot.'/'.$this->extensionName.'/'.$this->type.'/';

        $result = mkdir($extRoot . 'src', 0755);

        $robo->sayIt("Extension directory ($extRoot) created successfully.");

        $result = $result && mkdir($extRoot . 'src/Migrations', 0755);
        $result = $result && mkdir($extRoot . 'src/Common', 0755);
        $result = $result && mkdir($extRoot . 'src/Common/Model', 0755);
        $result = $result && mkdir($extRoot . 'web', 0755);
        $result = $result && mkdir($extRoot . 'web/js', 0755);
        if (!$result) {
            throw new \Exception('Could not create extension sub directories :'.$this->extensionsRoot.'/'.$this->extensionName.'/'.$this->type);
        }

        $robo->sayIt('Creating '.'src/Extension.php');
        file_put_contents(
            $extRoot.'src/Extension.php',
            (new PhpFile($this->getExtensionClassContent($formatter)))->getCode($formatter)
        );

        $robo->sayIt('Creating '.'src/Controller.php');
        file_put_contents(
            $extRoot.'src/Controller.php',
            $this->getContentByTemplate(
                'src/Controller.php',
                [
                    'name' => $this->extensionName,
                    'namespace' => $this->getNamespace($this->extensionName, $this->type),
                ]
            )
        );

        $robo->sayIt('Creating '.'src/Controller.php');
        file_put_contents(
            $extRoot.'src/ApiController.php',
            $this->getContentByTemplate(
                'src/ApiController.php',
                [
                    'name' => $this->extensionName,
                    'namespace' => $this->getNamespace($this->extensionName, $this->type),
                ]
            )
        );

        $robo->sayIt('Creating '.'src/Controller.php');
        file_put_contents(
            $extRoot.'src/Migrations/CreateTables.php',
            (new PhpFile($this->getMigrationCreateTablesClassContent($formatter)))->getCode($formatter)
        );

        $robo->sayIt('Creating '.$this->extensionName.'.php');
        file_put_contents(
            $extRoot.$this->extensionName.'.php',
            (new PhpFile($this->getExtensionMainFileContent($formatter)))->getCode($formatter)
        );

        $robo->sayIt('Creating '.'meta.json');
        file_put_contents(
            $extRoot.'meta.json',
            $this->getContentByTemplate(
                $this->type.'-meta.json',
                [
                    'name' => $this->extensionName,
                    'namespace' => $this->getNamespace($this->extensionName, $this->type),
                    'extension_menu_name' => $this->getExtensionMenuName($this->extensionName, $this->type),
                    'extension_main_menu_name' => $this->getExtensionMainMenuName($this->extensionName),
                    'variable_name' => $this->getVariableNameFromExtensionName($this->extensionName),
                ]
            )
        );

        $robo->sayIt('Creating '.'web/index.php');
        file_put_contents(
            $extRoot.'web/index.php',
            $this->getWebIndexFileContent($formatter)
        );

        $robo->sayIt('Creating '.'web/js/index.js');
        file_put_contents(
            $extRoot.'web/js/index.js',
            $this->getContentByTemplate(
                'web/js/index.js',
                [
                    'name' => $this->extensionName,
                    'type' => $this->type,
                    'namespace' => $this->getNamespace($this->extensionName, $this->type),
                    'variable_name' => $this->getVariableNameFromExtensionName($this->extensionName),
                ]
            )
        );

        $robo->sayIt('Creating '.'web/js/controller.js');
        file_put_contents(
            $extRoot.'web/js/controller.js',
            $this->getContentByTemplate(
                'web/js/controller.js',
                [
                    'name' => $this->extensionName,
                    'namespace' => $this->getNamespace($this->extensionName, $this->type),
                    'variable_name' => $this->getVariableNameFromExtensionName($this->extensionName),
                ]
            )
        );

        $robo->sayIt('Creating '.'web/js/module.js');
        file_put_contents(
            $extRoot.'web/js/module.js',
            $this->getContentByTemplate(
                'web/js/module.js',
                [
                    'name' => $this->extensionName,
                    'namespace' => $this->getNamespace($this->extensionName, $this->type),
                    'variable_name' => $this->getVariableNameFromExtensionName($this->extensionName),
                ]
            )
        );

        $robo->sayIt('Creating '.'web/js/view.js');
        file_put_contents(
            $extRoot.'web/js/view.js',
            $this->getContentByTemplate(
                'web/js/view.js',
                [
                    'name' => $this->extensionName,
                    'namespace' => $this->getNamespace($this->extensionName, $this->type),
                    'variable_name' => $this->getVariableNameFromExtensionName($this->extensionName),
                ]
            )
        );
    }

    public function getExtensionClassContent(CodeFormatter $formatter): string {
        $namespace = $this->getNamespace($this->extensionName, $this->type);
        $className = 'Extension';


        $class = new PhpClass($namespace, $className, 'IceExtension');

        $class->addUse('Classes\BaseService');
        $class->addUse('Classes\IceExtension');
        $class->addUse($namespace.'\Migrations\CreateTables');

        $initializeMethod = new Method('initialize');
        $initializeMethod->setBody([
            '// BaseService::getInstance()->registerExtensionMigration(new CreateTables());',
        ]);
        $class->addMethod($initializeMethod);

        $setupModuleClassDefinitionsMethod = new Method('setupModuleClassDefinitions');
        $setupModuleClassDefinitionsMethod->setBody([
            '// $this->addModelClass(\'ClassName\');',
        ]);
        $class->addMethod($setupModuleClassDefinitionsMethod);

        $setupRestEndPointsMethod = new Method('setupRestEndPoints');
        $setupRestEndPointsMethod->setBody([
            '(new ApiController())->registerEndPoints();',
        ]);
        $class->addMethod($setupRestEndPointsMethod);

        return $class->getCode($formatter);
    }

    public function getControllerClassContent(CodeFormatter $formatter): string
    {
        $namespace = $this->getNamespace($this->extensionName, $this->type);
        $className = 'Controller';

        $class = new PhpClass($namespace, $className, 'IceController');
        $class->addUse('Classes\IceController');

        return $class->getCode($formatter);
    }

    public function getMigrationCreateTablesClassContent(CodeFormatter $formatter): string
    {
        $namespace = $this->getNamespace($this->extensionName, $this->type).'\\Migrations';
        $className = 'CreateTables';


        $class = new PhpClass($namespace, $className, 'AbstractMigration');
        $class->setInterfaces(['MigrationInterface']);

        $class->addUse('Classes\Migration\AbstractMigration');
        $class->addUse('Classes\Migration\MigrationInterface');

        $getNameMethod = new Method('getName');
        $getNameMethod->setBody([
            sprintf("return '%s_create_table';", $this->extensionName),
        ]);
        $class->addMethod($getNameMethod);

        $upMethod = new Method('up');
        $upMethod->setBody([
            'return true;',
        ]);
        $class->addMethod($upMethod);

        $downMethod = new Method('down');
        $downMethod->setBody([
            'return true;',
        ]);
        $class->addMethod($downMethod);

        return $class->getCode($formatter);
    }

    public function getExtensionMainFileContent(CodeFormatter $formatter): string
    {
        $lines = [];
        $lines[] = 'require_once __DIR__.\'/src/Extension.php\';';
        $lines[] = 'require_once __DIR__.\'/src/Controller.php\';';
        $lines[] = 'require_once __DIR__.\'/src/ApiController.php\';';
        $lines[] = '';
        $lines[] = '// Migrations';
        $lines[] = 'require_once __DIR__.\'/src/Migrations/CreateTables.php\';';
        $lines[] = '';

        return join(PHP_EOL, $lines);
    }

    public function getWebIndexFileContent(CodeFormatter $formatter): string
    {
        $namespace = $this->getNamespace($this->extensionName, $this->type);
        $lines = [];
        $lines[] = '<?php';
        $lines[] = 'use Classes\PermissionManager;';
        $lines[] = '';
        $lines[] = '$moduleData = [';
        $lines[] = $formatter->addTab('\'controller_url\' => CLIENT_BASE_URL.\'service.php\',');
        $lines[] = $formatter->addTab('\'user_level\' => $user->user_level,');
        $lines[] = '];';
        $lines[] = '?><div class="span9"><div id="content"></div></div>';
        $lines[] = '<script>';
        $lines[] = 'init'.$namespace.'(<?=json_encode($moduleData)?>);';
        $lines[] = '</script>';
        $lines[] = '';

        return join(PHP_EOL, $lines);
    }

    protected function getCamelCaseNameFromExtensionName(string $name): string
    {
        $parts = explode('-', $name);
        $parts = array_map(function($item) {
            return ucfirst($item);
        }, $parts);

        return join('', $parts);
    }

    protected function getNamespace(string $name, string $type): string
    {
        return $this->getCamelCaseNameFromExtensionName($name).ucfirst($type);
    }

    protected function getVariableNameFromExtensionName(string $name): string
    {
        return lcfirst($this->getCamelCaseNameFromExtensionName($name));
    }

    protected function getExtensionMenuName(string $name, string $type): string
    {
        $parts = explode('-', $name);
        $parts[] = $type;
        $parts = array_map(function($item) {
            return ucfirst($item);
        }, $parts);

        return join(' ', $parts);
    }

    protected function getExtensionMainMenuName(string $name): string
    {
        $parts = explode('-', $name);
        $parts = array_map(function($item) {
            return ucfirst($item);
        }, $parts);

        return join(' ', $parts);
    }

    protected function getContentByTemplate(string $template, array $data): string
    {
        $template = file_get_contents(TEMPLATE_PATH.'/'.$template.'.tpl');
        foreach ($data as $key => $value) {
            $template = str_replace('__'.$key.'__', $value, $template);
        }

        return $template;
    }
}