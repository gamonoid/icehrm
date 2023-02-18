<?php
include __DIR__ . '/../src/MyORM/MySqlActiveRecord.php';

// Create the connection
$connection = new \MyORM\MySqlActiveRecord();
$res = $connection->Connect('127.0.0.1:10012', 'root', 'root', 'phpactiverecord');
