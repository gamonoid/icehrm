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

    /**
     * Templates live beside the code that renders them, in src/Reports/templates/.
     *
     * They used to sit in the reports MODULE directories, selected by a branch on
     * $report->table. That branch could never work: it was written with a single "="
     * — an assignment, always truthy — so every call took the "UserReports" path AND
     * silently overwrote $report->table as a side effect. The admin branch was dead,
     * and its directory did not even contain a customTemplates folder. Both reports
     * modules are gone now (superseded by the advance_reports extension), so there is
     * one location and no branch: PayslipReport, the only subclass, renders payslips
     * for payrolls whose template has no stored data.
     */
    protected function initTemplateEngine($report)
    {
        $loader = new \Twig_Loader_Filesystem(APP_BASE_PATH . "src/Reports/templates/");

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
