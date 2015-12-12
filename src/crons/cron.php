<?php
include dirname(__FILE__).'/include.cron.php';

$cron = new Cron();
$crons = $cron->Find("status = ?",array('Enabled'));

if(!$crons){
    LogManager::getInstance()->info(CLIENT_NAME." error :".$cron->ErrorMsg());
}

LogManager::getInstance()->info(CLIENT_NAME." cron count :".count($crons));
foreach($crons as $cron){
    $count++;
    $iceCron = new IceCron($cron);
    LogManager::getInstance()->info(CLIENT_NAME." check cron :".$cron->name);
    if($iceCron->isRunNow()){
        LogManager::getInstance()->info(CLIENT_NAME." execute cron :".$cron->name);
        $iceCron->execute();
        sleep(1);
    }
}

