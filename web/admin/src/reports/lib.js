/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
/* global SignaturePad, modJs */
/* eslint-disable no-underscore-dangle */

import AdapterBase from '../../../api/AdapterBase';


/**
 * ReportAdapter
 */


class ReportAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this._construct();
  }

  _construct() {
    this._formFileds = [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'label', validation: '' }],
      ['parameters', { label: 'Parameters', type: 'fieldset', validation: 'none' }],
    ];
    this.remoteFieldsExists = false;
  }

  _initLocalFormFields() {
    this._formFileds = [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'label', validation: '' }],
      ['parameters', { label: 'Parameters', type: 'fieldset', validation: 'none' }],
    ];
  }

  setRemoteFieldExists(val) {
    this.remoteFieldsExists = val;
  }

  getDataMapping() {
    return [
      'id',
      'icon',
      'name',
      'details',
      'parameters',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: '', bSortable: false, sWidth: '22px' },
      { sTitle: 'Name', sWidth: '30%' },
      { sTitle: 'Details' },
      { sTitle: 'Parameters', bVisible: false },
    ];
  }


  getFormFields() {
    return this._formFileds;
  }

  processFormFieldsWithObject(object) {
    const that = this;
    this._initLocalFormFields();
    const len = this._formFileds.length;
    const fieldIDsToDelete = [];
    const fieldsToDelete = [];
    this.remoteFieldsExists = false;
    for (let i = 0; i < len; i++) {
      if (this._formFileds[i][1].type === 'fieldset') {
        const newFields = JSON.parse(object[this._formFileds[i][0]]);
        fieldsToDelete.push(this._formFileds[i][0]);
        newFields.forEach((entry) => {
          that._formFileds.push(entry);
          if (entry[1]['remote-source'] !== undefined && entry[1]['remote-source'] != null) {
            that.remoteFieldsExists = true;
          }
        });
      }
    }

    const tempArray = [];
    that._formFileds.forEach((entry) => {
      if (jQuery.inArray(entry[0], fieldsToDelete) < 0) {
        tempArray.push(entry);
      }
    });

    that._formFileds = tempArray;
  }


  renderForm(object) {
    const that = this;
    this.processFormFieldsWithObject(object);
    if (this.remoteFieldsExists) {
      const cb = function () {
        that.renderFormNew(object);
      };
      this.initFieldMasterData(cb);
    } else {
      this.initFieldMasterData();
      that.renderFormNew(object);
    }

    this.currentReport = object;
  }

  renderFormNew(object) {
    const that = this;
    const signatureIds = [];
    if (object == null || object === undefined) {
      this.currentId = null;
    }

    this.preRenderForm(object);

    let formHtml = this.templates.formTemplate;
    let html = '';
    const fields = this.getFormFields();

    for (let i = 0; i < fields.length; i++) {
      const metaField = this.getMetaFieldForRendering(fields[i][0]);
      if (metaField === '' || metaField === undefined) {
        html += this.renderFormField(fields[i]);
      } else {
        const metaVal = object[metaField];
        if (metaVal !== '' && metaVal != null && metaVal !== undefined && metaVal.trim() !== '') {
          html += this.renderFormField(JSON.parse(metaVal));
        } else {
          html += this.renderFormField(fields[i]);
        }
      }
    }
    formHtml = formHtml.replace(/_id_/g, `${this.getTableName()}_submit`);
    formHtml = formHtml.replace(/_fields_/g, html);


    let $tempDomObj;
    const randomFormId = this.generateRandom(14);
    if (!this.showFormOnPopup) {
      $tempDomObj = $(`#${this.getTableName()}Form`);
    } else {
      $tempDomObj = $('<div class="reviewBlock popupForm" data-content="Form"></div>');
      $tempDomObj.attr('id', randomFormId);
    }

    $tempDomObj.html(formHtml);


    $tempDomObj.find('.datefield').datepicker({ viewMode: 2 });
    $tempDomObj.find('.timefield').datetimepicker({
      language: 'en',
      pickDate: false,
    });
    $tempDomObj.find('.datetimefield').datetimepicker({
      language: 'en',
    });

    $tempDomObj.find('.colorpick').colorpicker();

    // $tempDomObj.find('.select2Field').select2();
    $tempDomObj.find('.select2Field').each(function () {
      $(this).select2().select2('val', $(this).find('option:eq(0)').val());
    });

    $tempDomObj.find('.select2Multi').each(function () {
      $(this).select2().on('change', function (e) {
        const parentRow = $(this).parents('.row');
        const height = parentRow.find('.select2-choices').height();
        parentRow.height(parseInt(height, 10));
      });
    });


    $tempDomObj.find('.signatureField').each(function () {
      // $(this).data('signaturePad',new SignaturePad($(this)));
      signatureIds.push($(this).attr('id'));
    });

    for (let i = 0; i < fields.length; i++) {
      if (fields[i][1].type === 'datagroup') {
        $tempDomObj.find(`#${fields[i][0]}`).data('field', fields[i]);
      }
    }

    if (this.showSave === false) {
      $tempDomObj.find('.saveBtn').remove();
    } else {
      $tempDomObj.find('.saveBtn').off();
      $tempDomObj.find('.saveBtn').data('modJs', this);
      $tempDomObj.find('.saveBtn').on('click', function () {
        if ($(this).data('modJs').saveSuccessItemCallback != null && $(this).data('modJs').saveSuccessItemCallback !== undefined) {
          $(this).data('modJs').save($(this).data('modJs').retriveItemsAfterSave(), $(this).data('modJs').saveSuccessItemCallback);
        } else {
          $(this).data('modJs').save();
        }

        return false;
      });
    }

    if (this.showCancel === false) {
      $tempDomObj.find('.cancelBtn').remove();
    } else {
      $tempDomObj.find('.cancelBtn').off();
      $tempDomObj.find('.cancelBtn').data('modJs', this);
      $tempDomObj.find('.cancelBtn').on('click', function () {
        $(this).data('modJs').cancel();
        return false;
      });
    }

    if (!this.showFormOnPopup) {
      $(`#${this.getTableName()}Form`).show();
      $(`#${this.getTableName()}`).hide();

      for (let i = 0; i < signatureIds.length; i++) {
        $(`#${signatureIds[i]}`)
          .data('signaturePad',
            new SignaturePad(document.getElementById(signatureIds[i])));
      }

      if (object !== undefined && object != null) {
        this.fillForm(object);
      }
    } else {
      // var tHtml = $tempDomObj.wrap('<div>').parent().html();
      // this.showMessage("Edit",tHtml,null,null,true);
      this.showMessage('Edit', '', null, null, true);

      $('#plainMessageModel .modal-body').html('');
      $('#plainMessageModel .modal-body').append($tempDomObj);


      for (let i = 0; i < signatureIds.length; i++) {
        $(`#${signatureIds[i]}`)
          .data('signaturePad',
            new SignaturePad(document.getElementById(signatureIds[i])));
      }

      if (object !== undefined && object != null) {
        this.fillForm(object, `#${randomFormId}`);
      }
    }

    this.postRenderForm(object, $tempDomObj);
  }

  getActionButtonsHtml(id, data) {
    let html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/download.png" style="cursor:pointer;" rel="tooltip" title="Download" onclick="modJs.edit(_id_);return false;"></img></div>';
    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  addSuccessCallBack(callBackData, serverData) {
    const fileName = serverData[0];
    let link;

    if (fileName.indexOf('https:') === 0) {
      link = `<a href="${fileName}" target="_blank" style="font-size:14px;font-weight:bold;">Download Report <img src="_BASE_images/download.png"></img> </a>`;
    } else {
      link = `<a href="${modJs.getCustomActionUrl('download', { file: fileName })}" target="_blank" style="font-size:14px;font-weight:bold;">Download Report <img src="_BASE_images/download.png"></img> </a>`;
    }
    link = link.replace(/_BASE_/g, this.baseUrl);

    if (this.currentReport.output === 'PDF' || this.currentReport.output === 'JSON') {
      this.showMessage('Download Report', link);
    } else {
      if (serverData[1].length === 0) {
        this.showMessage('Empty Report', 'There were no data for selected filters');
        return;
      }


      const tableHtml = `${link}<br/><br/><div class="box-body table-responsive" style="overflow-x:scroll;padding: 5px;border: solid 1px #DDD;"><table id="tempReportTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped"></table></div>`;

      // Delete existing temp report table
      $('#tempReportTable').remove();

      // this.showMessage("Report",tableHtml);

      $(`#${this.table}`).html(tableHtml);
      $(`#${this.table}`).show();
      $(`#${this.table}Form`).hide();

      // Prepare headers
      const headers = [];
      for (const index in serverData[1]) {
        headers.push({ sTitle: serverData[1][index] });
      }

      const data = serverData[2];


      const dataTableParams = {
        oLanguage: {
          sLengthMenu: '_MENU_ records per page',
        },
        aaData: data,
        aoColumns: headers,
        bSort: false,
        iDisplayLength: 15,
        iDisplayStart: 0,
      };

      $('#tempReportTable').dataTable(dataTableParams);

      $('.dataTables_paginate ul').addClass('pagination');
      $('.dataTables_length').hide();
      $('.dataTables_filter input').addClass('form-control');
      $('.dataTables_filter input').attr('placeholder', 'Search');
      $('.dataTables_filter label').contents().filter(function () {
        return (this.nodeType === 3);
      }).remove();
      $('.tableActionButton').tooltip();
    }
  }


  fillForm(object) {
    const fields = this.getFormFields();
    for (let i = 0; i < fields.length; i++) {
      if (fields[i][1].type === 'label') {
        $(`#${this.getTableName()}Form #${fields[i][0]}`).html(object[fields[i][0]]);
      } else {
        $(`#${this.getTableName()}Form #${fields[i][0]}`).val(object[fields[i][0]]);
      }
    }
  }
}


class ReportGenAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
    ];
  }

  getFormFields() {
    return [

    ];
  }

  getActionButtonsHtml(id, data) {
    let html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/download.png" style="cursor:pointer;" rel="tooltip" title="Download" onclick="download(_name_);return false;"></img></div>';
    html = html.replace(/_id_/g, id);
    html = html.replace(/_name_/g, data[1]);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }
}


module.exports = { ReportAdapter, ReportGenAdapter };
