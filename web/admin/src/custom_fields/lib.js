/**
 * Author: Thilina Hasantha
 */
import ReactCustomFieldAdapter from '../../../api/ReactCustomFieldAdapter';

/**
 * AssetTypeAdapter
 */

class CommonCustomFieldAdapter extends ReactCustomFieldAdapter {
  getDataMapping() {
    return [
      'id',
      'name',
      'type',
      'field_type',
      'field_label',
      'display',
      'display_order',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Object Type' },
      { sTitle: 'Field Type' },
      { sTitle: 'Field Label' },
      { sTitle: 'Display Status' },
      { sTitle: 'Priority' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Object Type',
        dataIndex: 'type',
        sorter: true,
      },
      {
        title: 'Field Label',
        dataIndex: 'field_label',
      },
      {
        title: 'Field Type',
        dataIndex: 'field_type',
      },
      {
        title: 'Display Status',
        dataIndex: 'display',
        sorter: true,
      },
      {
        title: 'Priority',
        dataIndex: 'display_order',
        sorter: true,
      },
    ];
  }

  setTypes(tables) {
    this.types = tables;
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['field_label', { label: 'Field Label', type: 'text', validation: '' }],
      ['type',
        {
          label: 'Object Type',
          type: 'select2',
          source: this.types,
        },
      ],
      ['field_type', { label: 'Field Type', type: 'select', source: [['text', 'Text Field'], ['textarea', 'Text Area'], ['select', 'Select'], ['select2', 'Select2'], ['select2multi', 'Multi Select'], ['fileupload', 'File Upload'], ['date', 'Date'], ['datetime', 'Date Time'], ['time', 'Time'], ['signature', 'Signature']] }],
      ['field_validation', {
        label: 'Validation', type: 'select2', validation: 'none', sort: 'none', 'null-label': 'Required', 'allow-null': true, source: [['none', 'None'], ['number', 'Number'], ['numberOrEmpty', 'Number or Empty'], ['float', 'Decimal'], ['email', 'Email'], ['emailOrEmpty', 'Email or Empty']],
      }],
      ['field_options', {
        label: 'Field Options',
        type: 'datagroup',
        form: [
          ['label', { label: 'Label', type: 'text', validation: '' }],
          ['value', { label: 'Value', type: 'text', validation: 'none' }],
        ],
        html: '<div id="#_id_#" class="panel panel-default"><div class="panel-body">#_delete_##_edit_#<span style="color:#999;font-size:13px;font-weight:bold">#_label_#</span>:#_value_#</div></div>',
        columns: [
          {
            title: 'Label',
            dataIndex: 'label',
            key: 'label',
          },
          {
            title: 'Option Value',
            dataIndex: 'value',
            key: 'value',
          },
        ],
        validation: 'none',
      }],
      ['display_order', { label: 'Priority', type: 'text', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['type',
        {
          label: 'Object Type',
          type: 'select2',
          'allow-null': true,
          source: this.types,
        },
      ],
    ];
  }

  forceInjectValuesBeforeSave(params) {
    const data = ['', {}];
    const options = [];
    let optionsData;

    data[1].label = params.field_label;
    data[1].type = params.field_type;
    data[1].validation = params.field_validation;
    if (['select', 'select2', 'select2multi'].indexOf(params.field_type) >= 0) {
      optionsData = (params.field_options === '' || params.field_options === undefined)
        ? [] : JSON.parse(params.field_options);
      for (const index in optionsData) {
        options.push([optionsData[index].value, optionsData[index].label]);
      }
      data[1].source = options;
    }
    if (params.field_validation == null || params.field_validation === undefined) {
      params.field_validation = '';
    }
    if (this.currentElement == null || this.currentElement.name == null || this.currentElement.name === '') {
      params.name = this.getNameFromFieldName(params.field_label);
    } else {
      params.name = this.currentElement.name;
    }

    data[0] = params.name;
    params.data = JSON.stringify(data);

    params.display = 'Form';

    params.display_order = parseInt(params.display_order);
    if (!Number.isInteger(params.display_order)) {
      params.display_order = 1;
    }

    return params;
  }

  getHelpTitle() {
    return this.gt('Custom Fields');
  }

  getHelpDescription() {
    return this.gt('Here you can define custom fields to store additional information.');
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/creating-a-custom-field-for-expense-requests/';
  }

}


module.exports = { CommonCustomFieldAdapter };
