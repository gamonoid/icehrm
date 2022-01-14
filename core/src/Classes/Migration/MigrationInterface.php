<?php

namespace Classes\Migration;

interface MigrationInterface
{
    public function getName();
    public function up();
    public function down();
    public function getLastError();
}
