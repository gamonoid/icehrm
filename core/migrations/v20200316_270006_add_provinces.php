<?php
namespace Classes\Migration;

class v20200316_270006_add_provinces extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        INSERT INTO `Province` (`code`, `name`, `country`) VALUES 
        ('AN', 'Andaman and Nicobar Islands','IN'),
        ('AP', 'Andhra Pradesh', 'IN'),
        ('AR', 'Arunachal Pradesh', 'IN'),
        ('AS', 'Assam', 'IN'),
        ('BR', 'Bihar', 'IN'),
        ('CH', 'Chandigarh', 'IN'), 
        ('CG', 'Chhattisgarh', 'IN'), 
        ('DN', 'Dadra and Nagar Haveli', 'IN'), 
        ('DD', 'Daman and Diu', 'IN'), 
        ('DL', 'Delhi', 'IN'),
        ('GA', 'Goa', 'IN'),
        ('GJ', 'Gujarat', 'IN'),
        ('HR', 'Haryana', 'IN'),
        ('HP', 'Himachal Pradesh', 'IN'),
        ('JK', 'Jammu and Kashmir', 'IN'),
        ('KA', 'Karnataka', 'IN'),
        ('KL', 'Kerala', 'IN'),
        ('LD', 'Lakshadweep Islands', 'IN'),
        ('MP', 'Madhya Pradesh', 'IN'),
        ('MH', 'Maharashtra', 'IN'),
        ('MN', 'Manipur', 'IN'),
        ('ML', 'Meghalaya', 'IN'),
        ('MZ', 'Mizoram', 'IN'),
        ('NL', 'Nagaland', 'IN'),
        ('NL', 'Nagaland', 'IN'),
        ('OR', 'Odisha', 'IN'),
        ('PY', 'Puducherry', 'IN'),
        ('PB', 'Punjab', 'IN'),
        ('RJ', 'Rajasthan', 'IN'),
        ('SK', 'Sikkim', 'IN'),
        ('TN', 'Tamil Nadu', 'IN'),
        ('TS', 'Telangana', 'IN'),
        ('TR', 'Tripura', 'IN'),
        ('UP', 'Uttar Pradesh', 'IN'),
        ('UK', 'Uttarakhand', 'IN'),
        ('WB', 'West Bengal', 'IN');
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
