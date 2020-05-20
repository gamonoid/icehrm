<?php


namespace Classes\Cron\Task;

use Candidates\Admin\Api\CandidatesActionManager;
use Candidates\Common\Email\CandidatesEmailSender;
use Candidates\Common\Model\Candidate;
use Classes\BaseService;
use Classes\Cron\IceTask;
use Employees\Common\Model\Employee;
use JobPositions\Common\Model\Job;
use Users\Common\Model\User;

class NewCandidateEmailTask implements IceTask
{

    public function execute($cron)
    {
        $candidate = new Candidate();
        $candidates = $candidate->Find('source = ? and emailSent = ?', [Candidate::SOURCE_APPLIED, 0]);
        foreach ($candidates as $candidate) {
            $job = new Job();
            $job->Load('id = ?', [$candidate->jobId]);

            $candidateActionManager = new CandidatesActionManager();
            $candidateActionManager->setBaseService(BaseService::getInstance());
            $candidateEmailSender = new CandidatesEmailSender(
                BaseService::getInstance()->getEmailSender(),
                $candidateActionManager
            );

            $candidate->emailSent = 1;
            $ok = $candidate->Save();

            if (!$ok) {
                continue;
            }

            $candidateEmailSender->sendNewCandidateUserEmail($job->title, $candidate);

            if (empty($job->hiringManager)) {
                continue;
            }

            $manager = new Employee();
            $manager->Load('id = ?', [$job->hiringManager]);

            $managerUser = new User();
            $managerUser->Load('employee = ?', [$manager->id]);

            if (empty($managerUser->email)) {
                continue;
            }

            $candidateEmailSender->sendNewCandidateManagerEmail(
                $job->title,
                $candidate,
                $manager->first_name,
                $managerUser->email
            );
        }
    }
}
