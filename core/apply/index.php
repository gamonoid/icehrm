<?php
include APP_BASE_PATH.'includes.inc.php';
if (!\Classes\BaseService::getInstance()->isModuleEnabled('admin', 'candidates')) {
    header('Location: /');
    exit();
}

$fileService = \Classes\FileService::getInstance();

$job = new \JobPositions\Common\Model\Job();
$job->Load("code = ?",array($_GET['ref']));

$jobMapping = '{"country":["Country","id","name"],"company":["CompanyStructure","id","name"],"employementType":["EmployementType","id","name"],"experienceLevel":["ExperienceLevel","id","name"],"jobFunction":["JobFunction","id","name"],"educationLevel":["EducationLevel","id","name"],"currency":["CurrencyType","id","name"]}';

$companyName = \Classes\SettingsManager::getInstance()->getSetting('Company: Name');
$companyDescription = \Classes\SettingsManager::getInstance()->getSetting('Company: Description');
$logoFileUrl = \Classes\UIManager::getInstance()->getCompanyLogoUrl();

$countryName = "country_Name";
$employementTypeName = "employementType_Name";
$experienceLevelName = "experienceLevel_Name";
$industryName = "industry_Name";
$jobFunctionName = "jobFunction_Name";
$educationLevelName = "educationLevel_Name";

if(empty($job->id)){
    $jobs = $job->Find("status = ? order by closingDate",array('Active'));
    $jobsArr = array();
    foreach($jobs as $j){
        $enrichedJob = $baseService->getElement('Job', $j->id, $jobMapping, true);
        $jobsArr[] = $enrichedJob;
    }

    $meta = new stdClass();
    $meta->title = \Classes\SettingsManager::getInstance()->getSetting('Company: Name');
    $meta->description = "Open positions at ".\Classes\SettingsManager::getInstance()->getSetting('Company: Name');
    $meta->url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $meta->imageUrl = \Classes\UIManager::getInstance()->getCompanyLogoUrl();

    include(APP_BASE_PATH.'apply/list.php');

}else{

    $hiringManager = null;
    if (!empty($job->hiringManager) && (empty($job->showHiringManager) || $job->showHiringManager === 'Yes')) {
        $hiringManager = new \Employees\Common\Model\Employee();
        $hiringManager->Load('id = ?', [$job->hiringManager]);
        $hiringManager = \Classes\FileService::getInstance()->updateSmallProfileImage($hiringManager);
    }

    $enrichedJob = $baseService->getElement('Job', $job->id, $jobMapping, true);

    if(!empty($job->attachment)){
        $job->attachment = $fileService->getFileUrl($job->attachment);
    }

    $comapny = null;


    $currency = null;
    if(!empty($job->currency)){
        $currency = new \Metadata\Common\Model\CurrencyType();
        $currency->Load("id = ?",array($job->currency));
    }

    $benifits = NULL;
    if(!empty($job->benefits)){
        $benifits = json_decode($job->benefits, true);
    }

    $parser = new \cebe\markdown\Markdown();
    $job->description = $parser->parse($job->description);
    $job->requirements = $parser->parse($job->requirements);

    $meta = new stdClass();
    $meta->title = $job->title;
    $meta->description = trim(preg_replace('/\s\s+/', ' ', $job->shortDescription));
    $meta->url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $meta->imageUrl = \Classes\UIManager::getInstance()->getCompanyLogoUrl();

    include(APP_BASE_PATH.'apply/job.php');
}