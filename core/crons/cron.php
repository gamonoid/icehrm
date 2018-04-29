<?php
include dirname(__FILE__).'/include.cron.php';

$cron = new \Model\Cron();
$crons = $cron->Find("status = ?",array('Enabled'));

if(!$crons){
    \Utils\LogManager::getInstance()->info(CLIENT_NAME." error :".$cron->ErrorMsg());
}

\Utils\LogManager::getInstance()->info(CLIENT_NAME." cron count :".count($crons));
foreach($crons as $cron){
    $count++;
    $iceCron = new \Classes\Cron\IceCron($cron);
    \Utils\LogManager::getInstance()->info(CLIENT_NAME." check cron :".$cron->name);
    if($iceCron->isRunNow()){
        \Utils\LogManager::getInstance()->info(CLIENT_NAME." execute cron :".$cron->name);
        $iceCron->execute();
        sleep(1);
    }
}

