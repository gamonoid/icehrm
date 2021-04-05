/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from '../../../api/AdapterBase';
import ObjectAdapter from '../../../api/ObjectAdapter';

class EmployeeDocumentAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'document',
      'details',
      'date_added',
      'status',
      'attachment',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Document' },
      { sTitle: 'Details' },
      { sTitle: 'Date Added' },
      { sTitle: 'Status' },
      { sTitle: 'Attachment', bVisible: false },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['document', { label: 'Document', type: 'select2', 'remote-source': ['Document', 'id', 'name', 'getDocumentTypesForUser'] }],
      // [ "date_added", {"label":"Date Added","type":"date","validation":""}],
      ['valid_until', { label: 'Valid Until', type: 'date', validation: 'none' }],
      ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive'], ['Draft', 'Draft']] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
      ['attachment', { label: 'Attachment', type: 'fileupload', validation: '' }],
    ];
  }


  getActionButtonsHtml(id, data) {
    const downloadButton = '<img class="tableActionButton" src="_BASE_images/download.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Download Document" onclick="download(\'_attachment_\');return false;"></img>';
    const editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
    const deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
    let html = '<div style="width:80px;">_edit__download__delete_</div>';

    html = html.replace('_download_', downloadButton);

    if (this.showDelete) {
      html = html.replace('_delete_', deleteButton);
    } else {
      html = html.replace('_delete_', '');
    }

    if (this.showEdit) {
      html = html.replace('_edit_', editButton);
    } else {
      html = html.replace('_edit_', '');
    }

    html = html.replace(/_id_/g, id);
    html = html.replace(/_attachment_/g, data[5]);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }
}


/**
 * EmployeeCompanyDocumentAdapter
 */

class EmployeeCompanyDocumentAdapter extends ObjectAdapter {
  getDataMapping() {
    return [
      'id',
      'name',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'placeholder', validation: '' }],
      ['details', { label: 'Details', type: 'placeholder', validation: 'none' }],
      ['attachment', { label: 'Attachment', type: 'placeholder', validation: 'none' }],
    ];
  }

  addDomEvents(object) {

  }

  getTemplateName() {
    return 'file.html';
  }

  preProcessTableData(row) {
    row.color = this.getColorByFileType(row.type);
    row.icon = this.getIconByFileType(row.type);
    row.details_long = this.nl2br(row.details);

    if (row.details.length > 30) {
      row.details = row.details.substring(0, 30);
    }

    if (row.size === undefined || row.size == null) {
      row.size = '';
    }
    return row;
  }
}


module.exports = {
  EmployeeDocumentAdapter,
  EmployeeCompanyDocumentAdapter,
};
