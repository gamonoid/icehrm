<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:44 PM
 */

namespace Reports\Admin\Api;

use Classes\BaseService;
use Classes\SettingsManager;
use Classes\UIManager;
use Utils\LogManager;

class PDFReportBuilder extends ReportBuilder
{

    protected $twig;

    protected function getDefaultData()
    {
        $defaultData = array();
        $defaultData['BASE_URL'] = BASE_URL;
        $defaultData['LOGO'] = UIManager::getInstance()->getCompanyLogoUrl();
        $defaultData['LOGO'] = str_replace("https:", "http:", $defaultData['LOGO']);
        $defaultData['companyName'] = SettingsManager::getInstance()->getSetting("Company: Name");
        LogManager::getInstance()->debug("Logo Url:".$defaultData['LOGO']);
        return $defaultData;
    }

    protected function initTemplateEngine($report)
    {
        if ($report->table = "UserReports") {
            $path = APP_BASE_PATH."modules/reports/customTemplates/";
        } else {
            $path = APP_BASE_PATH."admin/reports/customTemplates/";
        }
        $loader = new \Twig_Loader_Filesystem($path);

        if (defined('CACHE_THEME') && CACHE_THEME) {
            $twigOptions = array(
            );
        } else {
            $twigOptions = array(
                "cache"=>false
            );
        }
        $this->twig = new \Twig_Environment($loader, $twigOptions);
    }

    public function createReportFile($report, $data)
    {
        $fileFirstPart = "Report_".str_replace(" ", "_", $report->name)."-".date("Y-m-d_H-i-s");
        $fileName = $fileFirstPart.".html";

        $fileFullName = BaseService::getInstance()->getDataDirectory().$fileName;

        $this->initTemplateEngine($report);

        $template = $this->twig->loadTemplate($this->getTemplate());
        $result = $template->render($data);

        $fp = fopen($fileFullName, 'w');
        fwrite($fp, $result);
        fclose($fp);

        try {
            $fileFullNamePdf = BaseService::getInstance()->getDataDirectory().$fileFirstPart.".pdf";
            //Try generating the pdf
            LogManager::getInstance()->debug(
                "wkhtmltopdf 1:".print_r(WK_HTML_PATH." ".$fileFullName." ".$fileFullNamePdf, true)
            );
            exec(WK_HTML_PATH." ".$fileFullName." ".$fileFullNamePdf, $output, $ret);

            LogManager::getInstance()->debug("wkhtmltopdf 2:".print_r($output, true));
            LogManager::getInstance()->debug("wkhtmltopdf 3:".print_r($ret, true));

            if (file_exists($fileFullNamePdf)) {
                $fileName = $fileFirstPart.".pdf";
                $fileFullName = $fileFullNamePdf;
            }
        } catch (\Exception $exp) {
            LogManager::getInstance()->notifyException($exp);
        }
        return array($fileFirstPart, $fileName, $fileFullName);
    }
}
