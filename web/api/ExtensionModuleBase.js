import 'regenerator-runtime/runtime';
import ReactModalAdapterBase from './ReactModalAdapterBase';

class ExtensionModuleBase extends ReactModalAdapterBase {
  showExtensionView() {

  }
  get() {
    this.showExtensionView();
  }

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
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
    ];
  }
}

export default ExtensionModuleBase;
