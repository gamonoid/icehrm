import React from 'react';
import ReactDOM from 'react-dom';
import { Space, Tag } from 'antd';
import {
  EditOutlined, DeleteOutlined, CopyOutlined, MonitorOutlined,
} from '@ant-design/icons';
import AdapterBase from './AdapterBase';
import IceFormModal from '../components/IceFormModal';
import IceStepFormModal from '../components/IceStepFromModal';
import IceTable from '../components/IceTable';
import MasterDataReader from './MasterDataReader';


class ReactModalAdapterBase extends AdapterBase {
  static get MODAL_TYPE_NORMAL() { return 'Normal'; }

  static get MODAL_TYPE_STEPS() { return 'Steps'; }

  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.modalType = this.MODAL_TYPE_NORMAL;
    this.dataPipe = null;
    this.formInitialized = false;
    this.tableInitialized = false;
    this.access = [];
    this.localStorageEnabled = false;
    this.isV2 = true;
    this.masterDataReader = new MasterDataReader(this);
  }

  enableLocalStorage() {
    this.localStorageEnabled = true;
  }

  setModalType(type) {
    this.modalType = type;
  }

  setDataPipe(dataPipe) {
    this.dataPipe = dataPipe;
  }

  setAccess(access) {
    this.access = access;
  }

  hasAccess(type) {
    return this.access.indexOf(type) >= 0;
  }

  hasCustomButtons() {
    return false;
  }

  initTable() {
    if (this.tableInitialized) {
      return false;
    }
    const tableDom = document.getElementById(`${this.tab}Table`);
    if (tableDom) {
      this.tableContainer = React.createRef();
      let columns = this.getTableColumns();
      if (this.hasAccess('save')
        || this.hasAccess('delete')
        || this.hasAccess('element')
        || this.hasCustomButtons()
      ) {
        columns.push({
          title: 'Actions',
          key: 'actions',
          render: this.getTableActionButtonJsx(this),
        });
      }

      columns = columns.map((item) => {
        item.title = this.gt(item.title);
        return item;
      });

      ReactDOM.render(
        <IceTable
          ref={this.tableContainer}
          reader={this.dataPipe}
          columns={columns}
          adapter={this}
        >
          {this.getTableChildComponents()}
        </IceTable>,
        tableDom,
      );
    }

    this.tableInitialized = true;

    return true;
  }

  keepTableVisibleWhileShowingCustomView() {
    return false;
  }

  getFormLayout(viewOnly) {
    return 'horizontal';
  }

  initForm() {
    if (this.formInitialized) {
      return false;
    }
    this.formContainer = React.createRef();
    if (this.modalType === this.MODAL_TYPE_NORMAL) {
      ReactDOM.render(
        <IceFormModal
          title={this.title || undefined}
          ref={this.formContainer}
          fields={this.getFormFields()}
          adapter={this}
          formReference={this.formReference}
        />,
        document.getElementById(`${this.tab}Form`),
      );
    } else {
      ReactDOM.render(
        <IceStepFormModal
          ref={this.formContainer}
          fields={this.getMappedFields()}
          adapter={this}
          formReference={this.formReference}
        />,
        document.getElementById(`${this.tab}Form`),
      );
    }

    const filterDom = document.getElementById(`${this.tab}FilterForm`);
    if (filterDom && this.getFilters()) {
      this.filtersContainer = React.createRef();
      ReactDOM.render(
        <IceFormModal
          title={this.title || undefined}
          ref={this.filtersContainer}
          fields={this.getFilters()}
          adapter={this}
          saveCallback={(values, showError, closeModal) => {
            this.setFilter(values);
            this.filtersAlreadySet = true;
            this.get([]);
            this.tableContainer.current.setFilterData(values);
            closeModal();
          }}
        />,
        filterDom,
      );
    }

    this.formInitialized = true;
    return true;
  }

  getTableChildComponents() {
    return false;
  }

  reloadCurrentElement() {
    this.viewElement(this.currentId);
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        {adapter.hasAccess('save') && adapter.showEdit
          && (
          <Tag color="green" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
            <EditOutlined />
            {` ${adapter.gt('Edit')}`}
          </Tag>
          )}
        {adapter.hasAccess('element')
        && (
          <Tag color="blue" onClick={() => modJs.viewElement(record.id)} style={{ cursor: 'pointer' }}>
            <MonitorOutlined />
            {` ${adapter.gt('View')}`}
          </Tag>
        )}
        {adapter.hasAccess('delete') && adapter.showDelete
        && (
        <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
          <DeleteOutlined />
          {` ${adapter.gt('Delete')}`}
        </Tag>
        )}
        {adapter.hasAccess('save') && adapter.showAddNew
        && (
        <Tag color="cyan" onClick={() => modJs.copyRow(record.id)} style={{ cursor: 'pointer' }}>
          <CopyOutlined />
          {` ${adapter.gt('Copy')}`}
        </Tag>
        )}
      </Space>
    );
  }

  setTableLoading(value) {
    this.tableContainer.current.setLoading(value);
  }

  /**
   * Show the view form for an item
   * @method viewElement
   * @param id {int} id of the item to view
   */
  viewElement(id) {
    this.setTableLoading(true);
    this.currentId = id;
    this.getElement(id, {
      noRender: true,
      callBack: (element) => {
        this.showElement(element);
        this.setTableLoading(false);
      },
    });
  }

  showElement(element) {
    this.renderForm(element, true);
  }

  hideElement() {
    this.tableContainer.current.setCurrentElement(false);
  }

  /**
   * Show the edit form for an item
   * @method edit
   * @param id {int} id of the item to edit
   */
  edit(id) {
    this.setTableLoading(true);
    this.currentId = id;
    this.getElement(id, []);
  }

  getDefaultValues() {
    return null;
  }

  renderForm(object = null, viewOnly = false) {
    if (object == null) {
      this.currentId = null;
      this.currentElement = null;
      object = this.getDefaultValues();
    }
    this.setTableLoading(false);
    this.initForm();
    this.formContainer.current.setViewOnly(viewOnly);
    this.formContainer.current.show(object);
  }

  showFilters() {
    this.initForm();
    this.filtersContainer.current.show(this.filter);
  }

  resetFilters() {
    this.filter = this.origFilter;
    this.filtersAlreadySet = false;
    this.currentFilterString = '';
    this.get([]);
    this.tableContainer.current.setFilterData(this.filter);
  }

  get() {
    if (this.tableContainer && this.tableContainer.current) {
      this.tableContainer.current.setCurrentElement(null);
    }
    this.initTable();
    this.masterDataReader.updateAllMasterData()
      .then(() => {
        this.tableContainer.current.reload();
      });

    this.trackEvent('get', this.tab, this.table);
  }

  showLoader() {
    // $('#iceloader').show();
  }

  addActualFieldsForStepModal(steps, fields) {
    return steps.map((item) => {
      item.fields = item.fields.reduce((acc, fieldName) => {
        const field = fields.find(([name]) => name === fieldName);
        if (field) {
          acc.push(field);
        }
        return acc;
      }, []);

      return item;
    });
  }

  hasCustomTopButtons() {
    return false;
  }

  getCustomTopButtons() {
    return (<></>);
  }

  getFormOptions() {
    return {
      width: 1024,
      twoColumnLayout: false,
    };
  }

  getWidth() {
    return 800;
  }
}

export default ReactModalAdapterBase;
