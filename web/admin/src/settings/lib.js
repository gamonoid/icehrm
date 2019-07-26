/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';

/**
 * SettingAdapter
 */

class SettingAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'value',
      'description',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Value' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['value', { label: 'Value', type: 'text', validation: 'none' }],
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

  edit(id) {
    this.loadRemoteDataForSettings();
    super.edit(id);
  }


  fillForm(object) {
    const metaField = this.getMetaFieldForRendering('value');
    const metaVal = object[metaField];
    let formFields = null;

    if (metaVal !== '' && metaVal !== undefined) {
      formFields = [
        ['id', { label: 'ID', type: 'hidden' }],
        JSON.parse(metaVal),
      ];
    }

    super.fillForm(object, null, formFields);
    $('#helptext').html(object.description);
  }


  loadRemoteDataForSettings() {
    const fields = [];
    let field = null;
    fields.push(['country', { label: 'Country', type: 'select2multi', 'remote-source': ['Country', 'id', 'name'] }]);
    fields.push(['countryCompany', { label: 'Country', type: 'select2', 'remote-source': ['Country', 'code', 'name'] }]);
    fields.push(['currency', { label: 'Currency', type: 'select2multi', 'remote-source': ['CurrencyType', 'id', 'code+name'] }]);
    fields.push(['nationality', { label: 'Nationality', type: 'select2multi', 'remote-source': ['Nationality', 'id', 'name'] }]);
    fields.push(['supportedLanguage', {
      label: 'Value', type: 'select2', 'allow-null': false, 'remote-source': ['SupportedLanguage', 'name', 'description'],
    }]);

    for (const index in fields) {
      field = fields[index];
      if (field[1]['remote-source'] !== undefined && field[1]['remote-source'] !== null) {
        const key = `${field[1]['remote-source'][0]}_${field[1]['remote-source'][1]}_${field[1]['remote-source'][2]}`;
        this.fieldMasterDataKeys[key] = false;
        this.sourceMapping[field[0]] = field[1]['remote-source'];

        const callBackData = {};
        callBackData.callBack = 'initFieldMasterDataResponse';
        callBackData.callBackData = [key];

        this.getFieldValues(field[1]['remote-source'], callBackData);
      }
    }
  }


  getHelpLink() {
    return 'http://blog.icehrm.com/docs/settings/';
  }
}

module.exports = { SettingAdapter };
