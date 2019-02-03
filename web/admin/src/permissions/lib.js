/*
Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';

/**
 * PermissionAdapter
 */

class PermissionAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'user_level',
      'module_id',
      'permission',
      'value',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'User Level' },
      { sTitle: 'Module' },
      { sTitle: 'Permission' },
      { sTitle: 'Value' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['user_level', { label: 'User Level', type: 'placeholder', validation: 'none' }],
      ['module_id', { label: 'Module', type: 'placeholder', 'remote-source': ['Module', 'id', 'menu+name'] }],
      ['permission', { label: 'Permission', type: 'placeholder', validation: 'none' }],
      ['value', { label: 'Value', type: 'text', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['module_id', {
        label: 'Module', type: 'select2', 'allow-null': true, 'null-label': 'All Modules', 'remote-source': ['Module', 'id', 'menu+name'],
      }],
    ];
  }

  getActionButtonsHtml(id, data) {
    let html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img></div>';
    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }


  getMetaFieldForRendering(fieldName) {
    if (fieldName === 'value') {
      return 'meta';
    }
    return '';
  }


  fillForm(object) {
    super.fillForm(object);
    $('#helptext').html(object.description);
  }
}

module.exports = { PermissionAdapter };
