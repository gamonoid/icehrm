<?php
namespace Classes\Migration;

class v20210606_290002_add_aws_region extends AbstractMigration {

    public function up(){

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'System: AWS Region', 
        'us-east-1',
        'Amazon SWS Region used for file storage',
        '',
        'System'
        );
SQL;

        $result = true;
        foreach ($sql as $query) {
            $result = $result && $this->executeQuery($query);
        }

        return $result;
    }

    public function down(){
        return true;
    }

}
