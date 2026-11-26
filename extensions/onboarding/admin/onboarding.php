<?php
$dir = \Classes\BaseService::getInstance()->getExtensionSourceDirectory(__DIR__);

require_once $dir.'Extension.php';
require_once $dir.'Controller.php';

// Models
require_once $dir.'Common/Model/OnboardingTemplate.php';
require_once $dir.'Common/Model/Onboarding.php';
require_once $dir.'Common/Model/OnboardingStep.php';
require_once $dir.'Common/Model/OnboardingActivity.php';

// Services
require_once $dir.'Db.php';
require_once $dir.'UploadGuard.php';
require_once $dir.'OnboardingAccess.php';
require_once $dir.'Integrations.php';
require_once $dir.'WorkflowService.php';
require_once $dir.'OnboardingNotifier.php';
require_once $dir.'OnboardingService.php';
require_once $dir.'HireRecordRemover.php';
require_once $dir.'StepService.php';
require_once $dir.'OnboardingTaskCreator.php';
require_once $dir.'ApiController.php';
require_once $dir.'Cron/OnboardingTask.php';

// Migrations
require_once $dir.'Migrations/CreateTables.php';
require_once $dir.'Migrations/AddTrack.php';

// Hourly: sync linked steps, activate new hires on their start date, send reminders.
\Classes\Cron\CronRegistry::addCron(
    'Onboarding',
    new \OnboardingAdmin\Cron\OnboardingTask(),
    1,
    \Classes\Cron\IceCron::HOURLY
);
