/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';
import ReactModalAdapterBase from "../../../api/ReactModalAdapterBase";

/**
 * FieldNameAdapter
 */

class FieldNameAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'textOrig',
      'textMapped',
      'display',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Original Text' },
      { sTitle: 'Mapped Text' },
      { sTitle: 'Display Status' },
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
        title: 'Original Text',
        dataIndex: 'textOrig',
        sorter: true,
      },
      {
        title: 'Mapped Text',
        dataIndex: 'textMapped',
        sorter: true,
      },
      {
        title: 'Display Status',
        dataIndex: 'display',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['type', { label: 'Type', type: 'placeholder', validation: '' }],
      ['name', { label: 'Name', type: 'placeholder', validation: '' }],
      ['textOrig', { label: 'Original Text', type: 'placeholder', validation: '' }],
      ['textMapped', { label: 'Mapped Text', type: 'text', validation: '' }],
      ['display', { label: 'Display Status', type: 'select', source: [['Form', 'Show'], ['Hidden', 'Hidden']] }],
    ];
  }
}


module.exports = { FieldNameAdapter };
