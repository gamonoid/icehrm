<?php

namespace Classes\Migration;

use Attendance\Common\Model\Attendance;

class v20201017_271101_switch_off_photo_att extends AbstractMigration
{

    public function up()
    {
        $sql = <<<'SQL'
Update Settings set value = '0' where name = 'Attendance: Photo Attendance';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Update Attendance set image_in = NULL where image_in like 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAANAAAA%';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Update Attendance set image_out = NULL where image_out like 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAANAAAA%';
SQL;
        return $this->executeQuery($sql);

    }

    public function down()
    {
        return true;
    }
}
