<?php
if(!interface_exists('ReportBuilderInterface')){
	include_once MODULE_PATH.'/reportClasses/ReportBuilderInterface.php';
}
abstract class ReportBuilder implements ReportBuilderInterface{
	
	public function getData($report,$request){
		$query = $this->getMainQuery();
		$where = $this->getWhereQuery($request);
		$query.=" ".$where[0];
		return $this->execute($report, $query, $where[1]);
	}
	
	protected function execute($report, $query, $parameters){
		$report->DB()->SetFetchMode(ADODB_FETCH_ASSOC);
		LogManager::getInstance()->debug("Query: ".$query);
		LogManager::getInstance()->debug("Parameters: ".json_encode($parameters));
		$rs = $report->DB()->Execute($query,$parameters);
		if(!$rs){
			LogManager::getInstance()->info($report->DB()->ErrorMsg());
			return array("ERROR","Error generating report");
		}
		
		$reportNamesFilled = false;
		$columnNames = array();
		$reportData = array();
		foreach ($rs as $rowId => $row) {
			$reportData[] = array();
			if(!$reportNamesFilled){
				$countIt = 0;
				foreach ($row as $name=> $value){
					$countIt++;
					$columnNames[$countIt] = $name;
					$reportData[count($reportData)-1][] = $value;
				}
				$reportNamesFilled = true;
			}else{
				foreach ($row as $name=> $value){
					$reportData[count($reportData)-1][] = $this->transformData($name, $value);
				}
			}
		}
		
		
		array_unshift($reportData,$columnNames);
		
		return $reportData;
	}
	
	abstract public function getWhereQuery($request);
	
	abstract public function getMainQuery();
	
	public function transformData($name, $value){
		return $value;	
	}
}