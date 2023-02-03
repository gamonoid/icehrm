<?php

use Classes\Cron\CronRegistry;
use Classes\Cron\IceCron;
use Classes\Cron\Task\DocumentExpiryNotificationTask;
use Classes\Cron\Task\EmailSenderTask;
use Utils\LogManager;

include dirname(__FILE__).'/include.cron.php';

// Register default cron tasks
CronRegistry::addCron(
    'Email Sender Task',
    new EmailSenderTask(),
    1,
    IceCron::MINUTELY
);

CronRegistry::addCron(
    'Document Expire Alert',
    new DocumentExpiryNotificationTask(),
    1,
    IceCron::HOURLY
);

CronRegistry::addCron(
    'Payroll Processor',
    new \Classes\Cron\Task\PayrollProcessTask(),
    1,
    IceCron::MINUTELY
);

CronRegistry::syncWithDatabase();
$crons = array_values(CronRegistry::getRegisteredCrons());

LogManager::getInstance()->info('List of enabled crons:');
foreach($crons as $cron){
    LogManager::getInstance()->info($cron['cron']->name);
}

LogManager::getInstance()->debug(CLIENT_NAME." cron count :".count($crons));
foreach($crons as $cron){
    $iceCron = new IceCron($cron['cron'], $cron['exe']);
    LogManager::getInstance()->debug(CLIENT_NAME." check cron :".$cron['cron']->name);
    if($iceCron->isRunNow()){
        LogManager::getInstance()->debug(CLIENT_NAME." execute cron :".$cron['cron']->name);
        $iceCron->execute();
    }
}

