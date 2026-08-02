<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:44 PM
 */

namespace Reports\Admin\Api;

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

    // createReportFile() used to live here and shelled out to wkhtmltopdf via
    // exec(WK_HTML_PATH." ".$fileFullName." ".$fileFullNamePdf) with an unquoted,
    // report-name-derived filename. It was dead code — the only subclass
    // (Reports\User\Reports\PayslipReport) overrides createReportFile() and renders
    // natively with mPDF, and PDFReportBuilder is never instantiated directly. Removed
    // rather than repaired; anything that does reach the inherited
    // ReportBuilder::createReportFile() gets the CSV writer.
}
