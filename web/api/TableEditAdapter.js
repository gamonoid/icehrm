/* global modJs */
/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from './AdapterBase';
/**
 * TableEditAdapter
 */

class TableEditAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.cellDataUpdates = {};
    this.modulePath = '';
    this.rowFieldName = '';
    this.columnFieldName = '';
    this.rowTable = '';
    this.columnTable = '';
    this.valueTable = '';
    this.csvData = [];
    this.columnIDMap = {};
  }

  setModulePath(path) {
    this.modulePath = path;
  }

  setRowFieldName(name) {
    this.rowFieldName = name;
  }

  setTables(rowTable, columnTable, valueTable) {
    this.rowTable = rowTable;
    this.columnTable = columnTable;
    this.valueTable = valueTable;
  }

  setColumnFieldName(name) {
    this.columnFieldName = name;
  }

  getDataMapping() {
    return [
    ];
  }


  getFormFields() {
    return [
    ];
  }

  get() {
    this.getAllData();
  }

  getAllData(save) {
    let req = {};
    req.rowTable = this.rowTable;
    req.columnTable = this.columnTable;
    req.valueTable = this.valueTable;
    req = this.addAdditionalRequestData('getAllData', req);
    req.save = (save === undefined || save == null || save === false) ? 0 : 1;
    const reqJson = JSON.stringify(req);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getAllDataSuccessCallBack';
    callBackData.callBackFail = 'getAllDataFailCallBack';

    this.customAction('getAllData', this.modulePath, reqJson, callBackData);
  }

  getDataItem(row, column, allData) {
    const columnData = allData[1];
    const rowData = allData[0];
    const serverData = allData[2];

    if (column === -1) {
      return rowData[row].name;
    }
    return this.getDataItemByKeyValues(this.rowFieldName, rowData[row].id, this.columnFieldName, columnData[column].id, serverData);
  }

  getDataItemByKeyValues(rowKeyName, rowKeyVal, colKeyName, colKeyVal, data) {
    for (let i = 0; i < data.length; i++) {
      if (data[i][rowKeyName] === rowKeyVal && data[i][colKeyName] === colKeyVal) {
        return (data[i].amount !== undefined && data[i].amount != null) ? data[i].amount : '';
      }
    }
    return '';
  }

  getAllDataSuccessCallBack(allData) {
    const serverData = allData[2];
    const columnData = allData[1];
    const rowData = allData[0];
    const data = [];
    for (let i = 0; i < rowData.length; i++) {
      const row = [];
      for (let j = -1; j < columnData.length; j++) {
        row[j + 1] = this.getDataItem(i, j, allData);
      }
      data.push(this.preProcessTableData(row));
    }
    this.sourceData = serverData;


    this.tableData = data;
    this.setHeaders(columnData, rowData);
    this.createTable(this.getTableName());
    $(`#${this.getTableName()}Form`).hide();
    $(`#${this.getTableName()}`).show();

    this.csvData = [];

    let tmpRow = [];
    for (let i = 0; i < columnData.length; i++) {
      tmpRow.push(columnData[i].name);
    }
    tmpRow = this.modifyCSVHeader(tmpRow);
    this.csvData.push(tmpRow);

    for (let i = 0; i < data.length; i++) {
      this.csvData.push(data[i]);
    }
  }

  modifyCSVHeader(header) {
    return header;
  }

  getAllDataFailCallBack(callBackData, serverData) {

  }

  setHeaders(columns, rows) {
    const headers = [];
    headers.push({ sTitle: '', sWidth: '180px;' });
    let sclass = '';
    this.columnIDMap = {};
    for (let i = 0; i < columns.length; i++) {
      this.columnIDMap[columns[i].id] = i;
      if (columns[i].editable === undefined || columns[i].editable == null || columns[i].editable === 'Yes') {
        sclass = 'editcell';
      } else {
        sclass = '';
      }
      headers.push({
        sTitle: columns[i].name,
        sClass: sclass,
        fnCreatedCell(nTd, sData, oData, iRow, iCol) {
          $(nTd).data('colId', columns[iCol - 1].id);
          $(nTd).data('rowId', rows[iRow].id);
        },
      });
    }

    this.headers = headers;
  }

  getHeaders() {
    return this.headers;
  }

  createTable(elementId) {
    const data = this.getTableData();
    const headers = this.getHeaders();

    if (this.showActionButtons()) {
      headers.push({ sTitle: '', sClass: 'center' });
    }


    if (this.showActionButtons()) {
      for (let i = 0; i < data.length; i++) {
        data[i].push(this.getActionButtonsHtml(data[i][0], data[i]));
      }
    }
    let html = '';
    html = `${this.getTableTopButtonHtml()}<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>`;

    // Find current page
    const activePage = $(`#${elementId} .dataTables_paginate .active a`).html();
    let start = 0;
    if (activePage !== undefined && activePage != null) {
      start = parseInt(activePage, 10) * 15 - 15;
    }

    $(`#${elementId}`).html(html);

    const dataTableParams = {
      oLanguage: {
        sLengthMenu: '_MENU_ records per page',
      },
      aaData: data,
      aoColumns: headers,
      bSort: false,
      iDisplayLength: 15,
      iDisplayStart: start,
    };


    const customTableParams = this.getCustomTableParams();

    $.extend(dataTableParams, customTableParams);

    $(`#${elementId} #grid`).dataTable(dataTableParams);

    $('.dataTables_paginate ul').addClass('pagination');
    $('.dataTables_length').hide();
    $('.dataTables_filter input').addClass('form-control');
    $('.dataTables_filter input').attr('placeholder', 'Search');
    $('.dataTables_filter label').contents().filter(function () {
      return (this.nodeType === 3);
    }).remove();
    // $('.tableActionButton').tooltip();
    $(`#${elementId} #grid`).editableTableWidget();

    $(`#${elementId} #grid .editcell`).on('validate', function (evt, newValue) {
      return modJs.validateCellValue($(this), evt, newValue);
    });

    this.afterCreateTable(elementId);
  }

  afterCreateTable(elementId) {

  }

  addCellDataUpdate(colId, rowId, data) {
    this.cellDataUpdates[`${colId}=${rowId}`] = [colId, rowId, data];
  }

  addAdditionalRequestData(type, req) {
    return req;
  }

  sendCellDataUpdates() {
    let req = this.cellDataUpdates;
    req.rowTable = this.rowTable;
    req.columnTable = this.columnTable;
    req.valueTable = this.valueTable;
    req = this.addAdditionalRequestData('updateData', req);
    const reqJson = JSON.stringify(req);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'updateDataSuccessCallBack';
    callBackData.callBackFail = 'updateDataFailCallBack';
    this.showLoader();
    this.customAction('updateData', this.modulePath, reqJson, callBackData);
  }

  updateDataSuccessCallBack(callBackData, serverData) {
    this.hideLoader();
    modJs.cellDataUpdates = {};
    modJs.get();
  }

  updateDataFailCallBack(callBackData, serverData) {
    this.hideLoader();
  }

  sendAllCellDataUpdates() {
    let req = this.cellDataUpdates;
    req.rowTable = this.rowTable;
    req.columnTable = this.columnTable;
    req.valueTable = this.valueTable;
    req = this.addAdditionalRequestData('updateAllData', req);
    const reqJson = JSON.stringify(req);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'updateDataAllSuccessCallBack';
    callBackData.callBackFail = 'updateDataAllFailCallBack';
    this.showLoader();
    this.customAction('updateAllData', this.modulePath, reqJson, callBackData);
  }

  updateDataAllSuccessCallBack(callBackData, serverData) {
    this.hideLoader();
    modJs.cellDataUpdates = {};
    modJs.getAllData(true);
  }

  updateDataAllFailCallBack(callBackData, serverData) {
    this.hideLoader();
  }

  showActionButtons() {
    return false;
  }
}

export default TableEditAdapter;
