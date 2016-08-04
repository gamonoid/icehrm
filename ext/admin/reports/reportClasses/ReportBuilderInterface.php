<?php
interface ReportBuilderInterface{
	public function getData($report,$request);
	public function createReportFile($report, $data);
}

interface CSVReportBuilderInterface{
	public function getData($report,$request);
	public function createReportFile($report, $data);
	public function getMainQuery();
	public function getWhereQuery($request);
}

interface PDFReportBuilderInterface{
	public function getData($report,$request);
	public function createReportFile($report, $data);
	public function getTemplate();
}