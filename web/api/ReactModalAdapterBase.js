import React from 'react';
import ReactDOM from 'react-dom';
import {
  Alert, Card, Space, Tag, ConfigProvider, theme as antdTheme,
} from 'antd';
import {
  EditOutlined, DeleteOutlined, CopyOutlined, MonitorOutlined,
} from '@ant-design/icons';
import AdapterBase from './AdapterBase';
import IceFormModal from '../components/IceFormModal';
import IceStepFormModal from '../components/IceStepFromModal';
import IceTable from '../components/IceTable';
import MasterDataReader from './MasterDataReader';

const { Meta } = Card;

// When mounted inside the SPA shell in dark mode (window.__shellColorMode is set
// by the shell), these adapter modals/tables render in their OWN React roots —
// outside the shell's ConfigProvider — so they would default to the light theme.
// Wrap each rendered tree in a matching dark ConfigProvider. In the legacy app
// the global is undefined, so the wrapper is a no-op (light, unchanged).
export function shellThemeWrap(node) {
  if (typeof window === 'undefined' || window.__shellColorMode !== 'dark') return node;
  return (
    <ConfigProvider
      theme={{
        algorithm: antdTheme.darkAlgorithm,
        token: {
          colorPrimary: '#1976d2',
          // Match the shell's dark palette so surfaces are elevated (lighter)
          // above the page, not darker than it.
          colorBgLayout: '#1a212b',
          colorBgContainer: '#222b36',
          colorBgElevated: '#2a3441',
        },
      }}
    >
      {node}
    </ConfigProvider>
  );
}


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
    this.tableTopInitilized = false;
    // Optional DOM containers for the SPA shell to mount the module natively
    // (no iframe). Keys: Table, Form, FilterForm, TableTop. When null, falls
    // back to the legacy page's PHP-printed `#<tab><suffix>` divs (unchanged).
    this.containerOverrides = null;
  }

  // SPA shell: provide explicit mount containers instead of `#<tab>Table` etc.
  setContainers(overrides) {
    this.containerOverrides = overrides || null;
  }

  getContainerEl(suffix) {
    if (this.containerOverrides && this.containerOverrides[suffix]) {
      return this.containerOverrides[suffix];
    }
    const el = document.getElementById(`${this.tab}${suffix}`);
    if (el) {
      return el;
    }
    // Modal hosts (Form / FilterForm) can be needed where neither the shell nor
    // a legacy page provided a div — e.g. editing a candidate from the kanban
    // board tab. The modals render through portals, so a detached container
    // appended to body works; table containers stay null (callers guard).
    if (suffix === 'Form' || suffix === 'FilterForm') {
      if (!this.detachedContainers) {
        this.detachedContainers = {};
      }
      if (!this.detachedContainers[suffix]) {
        const div = document.createElement('div');
        document.body.appendChild(div);
        this.detachedContainers[suffix] = div;
      }
      return this.detachedContainers[suffix];
    }
    return null;
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
    const tmp = [];
    for (const index in access) {
      tmp.push(access[index]);
    }
    this.access = tmp;
  }

  hasAccess(type) {
    return this.access.indexOf(type) >= 0;
  }

  showViewButton() {
    return true;
  }

  hasCustomButtons() {
    return false;
  }

  getTableTopComponent() {
    if (this.getHelpTitle() === null && this.getHelpDescription() === null) {
      return null;
    }
    return (
      <Card size="small" title={this.getHelpTitle()} extra={<a href={this.getHelpLink()} target="_blank">{this.gt('More Info')}</a>} style={{ width: '100%' }}>
        <Meta description={this.getHelpDescription()} />
      </Card>
    );
  }

  getHelpTitle() {
    return null;
  }

  getHelpDescription() {
    return null;
  }

  initTableTopComponent() {
    if (this.tableTopInitilized === false && this.getTableTopComponent()) {
      const tableTop = this.getContainerEl('TableTop');
      if (tableTop) {
        ReactDOM.render(
          shellThemeWrap(this.getTableTopComponent()),
          tableTop,
        );

        this.tableTopInitilized = true;
      }
    }
  }

  initTable() {
    this.initTableTopComponent();
    if (this.tableInitialized) {
      return false;
    }
    const tableDom = this.getContainerEl('Table');
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
        shellThemeWrap(
          <IceTable
            ref={this.tableContainer}
            reader={this.dataPipe}
            columns={columns}
            adapter={this}
          >
            {this.getTableChildComponents()}
          </IceTable>,
        ),
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
        shellThemeWrap(
          <IceFormModal
            title={this.title || undefined}
            ref={this.formContainer}
            fields={this.getFormFields()}
            adapter={this}
            formReference={this.formReference}
          />,
        ),
        this.getContainerEl('Form'),
      );
    } else {
      // Adapters may supply a re-skinned step modal (e.g. the candidate wizard)
      // while keeping IceStepForm's field/validation mechanics.
      const StepModal = this.getStepFormModalComponent();
      ReactDOM.render(
        shellThemeWrap(
          <StepModal
            ref={this.formContainer}
            fields={this.getMappedFields()}
            adapter={this}
            formReference={this.formReference}
          />,
        ),
        this.getContainerEl('Form'),
      );
    }

    const filterDom = this.getContainerEl('FilterForm');
    if (filterDom && this.getFilters()) {
      this.filtersContainer = React.createRef();
      ReactDOM.render(
        shellThemeWrap(
          <IceFormModal
            title={this.title || undefined}
            ref={this.filtersContainer}
            fields={this.getFilters()}
            adapter={this}
            saveCallback={(values, showError, closeModal) => {
              // Format date/datetime/time filter fields to their string forms
              // (YYYY-MM-DD, ...) the same way the edit form does. The raw antd
              // values are moment objects that would otherwise serialise to
              // timezone-shifted ISO strings and never match server-side.
              const iceForm = this.filtersContainer && this.filtersContainer.current
                && this.filtersContainer.current.iceFormReference
                && this.filtersContainer.current.iceFormReference.current;
              const formatted = (iceForm && iceForm.formFieldsToData)
                ? iceForm.formFieldsToData({ ...values }, this.getFilters())
                : values;
              this.setFilter(formatted);
              this.filtersAlreadySet = true;
              this.get([]);
              this.setTableContainerFilterData(formatted);
              closeModal();
            }}
          />,
        ),
        filterDom,
      );
    }

    this.formInitialized = true;
    return true;
  }

  getStepFormModalComponent() {
    return IceStepFormModal;
  }

  // Resolve a named modal-host div by id, creating a detached one when the
  // legacy page's PHP-printed div is absent (SPA shell). Modals render through
  // portals, so the detached host is sufficient.
  getDomContainer(id) {
    let el = document.getElementById(id);
    if (!el) {
      el = document.createElement('div');
      el.id = id;
      document.body.appendChild(el);
    }
    return el;
  }

  setTableContainerFilterData(values) {
    this.tableContainer.current.setFilterData(values);
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
        {adapter.hasAccess('element') && adapter.showViewButton()
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
    if (this.tableContainer && this.tableContainer.current) {
      this.tableContainer.current.setLoading(value);
    }
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
    object = this.modifyObjectBeforeView(object, viewOnly);
    this.setTableLoading(false);
    this.initForm();
    this.formContainer.current.setViewOnly(viewOnly);
    this.formContainer.current.show(object);
  }

  modifyObjectBeforeView( object, viewOnly ) {
    return object;
  }

  showFilters() {
    this.initForm();
    if (this.filtersContainer && this.filtersContainer.current) {
      // Strip 'NULL'/empty stored values before repopulating the form so unset
      // fields render their placeholder — otherwise a select shows the literal
      // "NULL" and an empty date parses to "Invalid date".
      let initial = this.filter;
      if (initial && typeof initial === 'object') {
        initial = Object.keys(initial).reduce((acc, k) => {
          if (initial[k] !== 'NULL' && initial[k] !== '' && initial[k] != null) {
            acc[k] = initial[k];
          }
          return acc;
        }, {});
      }
      this.filtersContainer.current.show(initial);
    } else {
      console.warn('Filter form container not available. Make sure FilterForm div exists in HTML and getFilters() returns filters.');
    }
  }

  resetFilters() {
    this.filter = this.origFilter;
    this.filtersAlreadySet = false;
    this.currentFilterString = '';
    this.get([]);
    this.setTableContainerFilterData(this.filter);
  }

  get() {
    if (this.tableContainer && this.tableContainer.current) {
      this.tableContainer.current.setCurrentElement(null);
    }
    this.initTable();
    this.masterDataReader.updateAllMasterData()
      .then(() => {
        if (this.tableContainer && this.tableContainer.current) {
          this.tableContainer.current.reload();
        }
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

  getViewModeEnabledFields() {
    return null;
  }

  getViewModeShowLabel() {
    return true;
  }
}

export default ReactModalAdapterBase;
