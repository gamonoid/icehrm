<?php


namespace Classes\Cron\Task;

use Candidates\Admin\Api\CandidatesActionManager;
use Candidates\Common\Email\CandidatesEmailSender;
use Candidates\Common\Model\Candidate;
use Candidates\Common\Model\Interview;
use Classes\BaseService;
use Classes\Cron\IceTask;
use Employees\Common\Model\Employee;
use JobPositions\Common\Model\Job;
use Users\Common\Model\User;
use Utils\LogManager;

class RecruitmentEmailTask implements IceTask
{
    public function execute($cron)
    {
        $interview = new Interview();
        $interviews = $interview->Find('scheduleUpdated = ?', [1]);
        foreach ($interviews as $interview) {
            $job = new Job();
            $job->Load('id = ?', [$interview->job]);

            $candidate = new Candidate();
            $candidate->Load('id = ?', [$interview->candidate]);

            $manager = new Employee();
            $manager->Load('id = ?', [$job->hiringManager]);

            $managerUser = new User();
            $managerUser->Load('employee = ?', [$manager->id]);

            $candidateActionManager = new CandidatesActionManager();
            $candidateActionManager->setBaseService(BaseService::getInstance());
            $candidateEmailSender = new CandidatesEmailSender(
                BaseService::getInstance()->getEmailSender(),
                $candidateActionManager
            );

            $interview->scheduleUpdated = 0;
            $ok = $interview->Save();

            if (!$ok) {
                continue;
            }

            if (!empty($job->hiringManager)) {
                $manager = new Employee();
                $manager->Load('id = ?', [$job->hiringManager]);

                $managerUser = new User();
                $managerUser->Load('employee = ?', [$manager->id]);

                if (empty($managerUser->email)) {
                    continue;
                }

                $candidateEmailSender->interviewScheduledManagerEmail(
                    $job->title,
                    $candidate,
                    $manager->first_name,
                    $managerUser->email,
                    $interview
                );
            }

            if (empty($interview->interviewers)) {
                continue;
            }

            $interviewerIds = null;
            try {
                $interviewerIds = json_decode($interview->interviewers, true);
            } catch (\Exception $e) {
                LogManager::getInstance()->notifyException($e);
            }

            if (empty($interviewerIds) && !is_array($interviewerIds)) {
                continue;
            }

            foreach ($interviewerIds as $interviewerId) {
                $interviewer = new Employee();
                $interviewer->Load('id = ?', [$interviewerId]);

                $interviewerUser = new User();
                $interviewerUser->Load('employee = ?', [$interviewer->id]);

                if (empty($interviewerUser->email)) {
                    continue;
                }

                $candidateEmailSender->interviewScheduledInterviewerEmail(
                    $job->title,
                    $candidate,
                    $manager->first_name,
                    $managerUser->email,
                    $interviewerUser->email,
                    $interview,
                    $interviewer
                );
            }
        }
    }
}
