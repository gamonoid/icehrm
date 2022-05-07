<?php
namespace Classes;

use Reports\Admin\Api\CSVReportBuilder;
use Reports\Admin\Api\ReportBuilder;
use Reports\Admin\Api\ReportBuilderInterface;
use Utils\LogManager;

class ReportHandler
{
    public function handleReport($request)
    {
        if (!empty($request['id'])) {
            $reportMgrClass = BaseService::getInstance()->getFullQualifiedModelClassName($request['t']);
            /* @var \Model\Report $report */
            $report = new $reportMgrClass();
            $report->Load("id = ?", array($request['id']));
            if ($report->id."" == $request['id']) {
                if ($report->type == 'Query') {
                    $where = $this->buildQueryOmmit(json_decode($report->paramOrder, true), $request);
                    $query = str_replace("_where_", $where[0], $report->query);
                    return $this->executeReport(new CSVReportBuilder(), $report, $query, $where[1]);
                } elseif ($report->type == 'Class') {
                    $className = $report->query;

                    if ($request['t'] == "Report") {
                        $className = '\\Reports\\Admin\\Reports\\'.$className;
                    } else {
                        $className = '\\Reports\\User\\Reports\\'.$className;
                    }

                    $cls = new $className();
                    $data = $cls->getData($report, $request);
                    if (empty($data)) {
                        return array("ERROR", "No data found");
                    }
                    return $this->generateReport($cls, $report, $data);
                }
            } else {
                return array("ERROR","Report id not found");
            }
        }
    }

    private function executeReport($reportBuilder, $report, $query, $parameters)
    {

        // $report->DB()->SetFetchMode(ADODB_FETCH_ASSOC);
        $rs = $report->DB()->Execute($query, $parameters);
        if (!$rs) {
            LogManager::getInstance()->info($report->DB()->ErrorMsg());
            return array("ERROR","Error generating report");
        }

        $reportNamesFilled = false;
        $columnNames = array();
        $reportData = array();
        foreach ($rs as $rowId => $row) {
            $reportData[] = array();
            if (!$reportNamesFilled) {
                foreach ($row as $name => $value) {
                    $columnNames[] = $name;
                    $reportData[count($reportData)-1][] = $value;
                }
                $reportNamesFilled = true;
            } else {
                foreach ($row as $name => $value) {
                    $reportData[count($reportData)-1][] = $value;
                }
            }
        }

        array_unshift($reportData, $columnNames);

        return $this->generateReport($reportBuilder, $report, $reportData);
    }

    /**
     * @param ReportBuilder $reportBuilder
     * @param $report
     * @param $data
     * @return array
     */
    protected function generateReport($reportBuilder, $report, $data)
    {

        $reportCreationData = $reportBuilder->createReportFile($report, $data);

        $saveResp = $reportBuilder->saveFile($reportCreationData[0], $reportCreationData[1], $reportCreationData[2]);

        $headers = array_shift($data);

        return array($saveResp[0],array($saveResp[1],$headers,$data));
    }

    private function buildQueryOmmit($names, $params)
    {
        $parameters = array();
        $query = "";
        foreach ($names as $name) {
            if ($params[$name] != "NULL") {
                if ($query != "") {
                    $query.=" AND ";
                }
                $query.=$name." = ?";
                $parameters[] = $params[$name];
            }
        }

        if ($query != "") {
            $query = "where ".$query;
        }

        return array($query, $parameters);
    }
}
