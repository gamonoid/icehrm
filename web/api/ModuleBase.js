/* global tinymce, SimpleMDE, modJs, SignaturePad, modulesInstalled */
/* eslint-disable prefer-destructuring */
/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import FormValidation from './FormValidation';
/**
 * The base class for providing core functions to all module classes.
 * @class Base.js
 */

class ModuleBase {
  constructor() {
    this.deleteParams = {};
    this.createRemoteTable = false;
    this.instanceId = 'None';
    this.ga = [];
    this.showAddNew = true;
    this.showEdit = true;
    this.showDelete = true;
    this.showSave = true;
    this.showCancel = true;
    this.showFormOnPopup = false;
    this.filtersAlreadySet = false;
    this.currentFilterString = '';
    this.sorting = 0;
    this.settings = {};
    this.translations = {};
    this.customFields = [];
    this.csrfRequired = false;

    this.fieldTemplates = null;
    this.templates = null;
    this.customTemplates = null;
    this.emailTemplates = null;
    this.fieldMasterData = {};
    this.fieldMasterDataKeys = {};
    this.fieldMasterDataCallback = null;
    this.sourceMapping = null;
    this.currentId = null;
    this.currentElement = null;
    this.user = null;
    this.currentProfile = null;
    this.permissions = {};
    this.baseUrl = null;
    this.clientUrl = null;
    this.that = this;
  }

  // eslint-disable-next-line no-unused-vars
  init(appName, currentView, dataUrl, permissions) {

  }

  initForm() {

  }

  setObjectTypeName(objectTypeName) {
    this.objectTypeName = objectTypeName;
  }

  /**
     * Some browsers do not support sending JSON in get parameters. Set this to true to avoid sending JSON
     * @method setNoJSONRequests
     * @param val {Boolean}
     */
  setNoJSONRequests(val) {
    this.noJSONRequests = val;
  }


  setPermissions(permissions) {
    this.permissions = permissions;
  }

  sortingStarted(val) {
    this.sorting = val;
  }

  /**
     * Check if the current user has a permission
     * @method checkPermission
     * @param permission {String}
     * @example
     * this.checkPermission("Upload/Delete Profile Image")
     */
  checkPermission(permission) {
    if (this.permissions[permission] === undefined || this.permissions[permission] == null || this.permissions[permission] === 'Yes') {
      return 'Yes';
    }
    return this.permissions[permission];
  }

  setBaseUrl(url) {
    this.baseUrl = url;
  }

  setClientUrl(url) {
    this.clientUrl = url;
  }

  setUser(user) {
    this.user = user;
  }

  getUser() {
    return this.user;
  }

  setInstanceId(id) {
    this.instanceId = id;
  }

  setCSRFRequired(val) {
    this.csrfRequired = val;
  }

  slowScrollToTop() {
    $('html, body').animate({ scrollTop: 0 }, 'slow');
  }

  scrollToTop() {
    $('html, body').animate({ scrollTop: 0 }, 'fast');
  }

  scrollToBottom() {
    $('html, body').animate({ scrollTop: $(document).height() }, 'slow');
  }

  scrollToElement(element) {
    if ($(window).height() <= element.offset().top) {
      $('html, body').animate({ scrollTop: element.offset().top }, 'slow');
    }
  }

  scrollToElementBottom(element) {
    if ($(window).height() <= element.offset().top + element.height()) {
      $('html, body').animate({ scrollTop: element.offset().top + element.height() }, 'slow');
    }
  }


  setTranslations(txt) {
    this.translations = txt.messages[''];
  }

  setTranslationsSubModules(translations) {
    this.translations = translations;
  }

  gt(key) {
    if (this.translations[key] === undefined || this.translations[key] === null) {
      console.log(`Tr:${key}`);
      return key;
    }
    return this.translations[key][0];
  }

  addToLangTerms(key) {
    let termsArr;
    const terms = localStorage.getItem('terms');
    if (terms === undefined) {
      termsArr = {};
    } else {
      try {
        termsArr = JSON.parse(terms);
      } catch (e) {
        termsArr = {};
      }
    }

    if (this.translations[key] === undefined) {
      termsArr[key] = key;
      localStorage.setItem('terms', JSON.stringify(termsArr));
    }
  }

  /**
     * If this method returned false the action buttons in data table for modules will not be displayed.
     * Override this method in module lib.js to hide action buttons
     * @method showActionButtons
     * @param permission {String}
     * @example
     * EmployeeLeaveEntitlementAdapter.method('showActionButtons() {
     *  return false;
     * }
     */
  showActionButtons() {
    return true;
  }

  trackEvent(action, label, value) {
    try {
      if (label === undefined || label == null) {
        this.ga.push(['_trackEvent', this.instanceId, action]);
      } else if (value === undefined || value == null) {
        this.ga.push(['_trackEvent', this.instanceId, action, label]);
      } else {
        this.ga.push(['_trackEvent', this.instanceId, action, label, value]);
      }
    } catch (e) {
      // Do nothing
    }
  }


  setCurrentProfile(currentProfile) {
    this.currentProfile = currentProfile;
  }

  /**
     * Get the current profile
     * @method getCurrentProfile
     * @returns Profile of the current user if the profile is not switched if not switched profile
     */

  getCurrentProfile() {
    return this.currentProfile;
  }

  /**
     * Retrive data required to create select boxes for add new /edit forms for a given module. This is called when loading the module
     * @method initFieldMasterData
     * @param callback {Function} call this once loading completed
     * @param callback {Function} call this once all field loading completed. This indicate that the form can be displayed saftly
     * @example
     *   ReportAdapter.method('renderForm(object) {
     *    var that = this;
     *    this.processFormFieldsWithObject(object);
     *    var cb = function(){
     *      that.super.renderForm(object);
     *    };
     *    this.initFieldMasterData(cb);
     *  }
     */
  initFieldMasterData(callback, loadAllCallback, loadAllCallbackData) {
    this.fieldMasterData = {};
    this.fieldMasterDataKeys = {};
    this.fieldMasterDataCallback = loadAllCallback;
    this.fieldMasterDataCallbackData = loadAllCallbackData;

    const remoteSourceFields = this.getRemoteSourceFields();

    for (let i = 0; i < remoteSourceFields.length; i++) {
      const fieldRemote = remoteSourceFields[i];
      if (fieldRemote[1]['remote-source'] !== undefined && fieldRemote[1]['remote-source'] != null) {
        // let key = `${fieldRemote[1]['remote-source'][0]}_${fieldRemote[1]['remote-source'][1]}_${fieldRemote[1]['remote-source'][2]}`;
        // if (fieldRemote[1]['remote-source'].length === 4) {
        //   key = `${key}_${fieldRemote[1]['remote-source'][3]}`;
        // }
        const key = this.getRemoteSourceKey(fieldRemote);
        this.fieldMasterDataKeys[key] = false;

        const callBackData = {};
        callBackData.callBack = 'initFieldMasterDataResponse';
        callBackData.callBackData = [key];
        if (callback !== null && callback !== undefined) {
          callBackData.callBackSuccess = callback;
        }
        this.getFieldValues(fieldRemote[1]['remote-source'], callBackData);
      }
    }
  }


  initSourceMappings() {
    this.sourceMapping = {};

    const remoteSourceFields = this.getRemoteSourceFields();

    for (let i = 0; i < remoteSourceFields.length; i++) {
      const fieldRemote = remoteSourceFields[i];
      if (fieldRemote[1]['remote-source'] !== undefined && fieldRemote[1]['remote-source'] != null) {
        this.sourceMapping[fieldRemote[0]] = fieldRemote[1]['remote-source'];
      }
    }
  }

  getRemoteSourceKey(field) {
    let key = `${field[1]['remote-source'][0]}_${field[1]['remote-source'][1]}_${field[1]['remote-source'][2]}`;
    if (field[1]['remote-source'].length > 3) {
      key = `${key}_${field[1]['remote-source'][3]}`;
    }

    return key;
  }

  getRemoteSourceFields() {
    let values;
    const fields = this.getFormFields();
    const filterFields = this.getFilters();
    const additionalFields = this.getAdditionalRemoteFields();

    if (filterFields != null) {
      for (let j = 0; j < filterFields.length; j++) {
        values = filterFields[j][1];
        if (values.type === 'select' || values.type === 'select2' || values.type === 'select2multi') {
          fields.push(filterFields[j]);
        }
      }
    }

    if (additionalFields != null) {
      for (let j = 0; j < additionalFields.length; j++) {
        values = additionalFields[j][1];
        if (values.type === 'select' || values.type === 'select2' || values.type === 'select2multi') {
          fields.push(additionalFields[j]);
        }
      }
    }

    const remoteSourceFields = [];
    const remoteSourceFieldKeys = [];
    let field = null;
    let fieldSub = null;
    for (let i = 0; i < fields.length; i++) {
      field = fields[i];
      if (field[1]['remote-source'] !== undefined && field[1]['remote-source'] !== null) {
        const key = this.getRemoteSourceKey(field);
        if (remoteSourceFieldKeys.indexOf(key) < 0) {
          remoteSourceFields.push(field);
          remoteSourceFieldKeys.push(key);
        }
      } else if (field[1].form !== undefined && field[1].form !== null) {
        for (let j = 0; j < field[1].form.length; j++) {
          fieldSub = field[1].form[j];
          if (fieldSub[1]['remote-source'] !== undefined && fieldSub[1]['remote-source'] !== null) {
            const key = this.getRemoteSourceKey(fieldSub);
            if (remoteSourceFieldKeys.indexOf(key) < 0) {
              remoteSourceFields.push(fieldSub);
              remoteSourceFieldKeys.push(key);
            }
          }
        }
      }
    }

    return remoteSourceFields;
  }

  /**
     * Pass true to this method after creating module JS object to open new/edit entry form for the module on a popup.
     * @method setShowFormOnPopup
     * @param val {Boolean}
     * @example
     *   modJs.subModJsList['tabCandidateApplication'] = new CandidateApplicationAdapter('Application','CandidateApplication',{"candidate":data.id}
     *  modJs.subModJsList['tabCandidateApplication'].setShowFormOnPopup(true);
     */

  setShowFormOnPopup(val) {
    this.showFormOnPopup = val;
  }

  /**
     * Set this to true to if you need the datatable to load data page by page instead of loading all data at once.
     * @method setRemoteTable
     * @param val {Boolean}
     * @example
     *   modJs.subModJsList['tabCandidateApplication'] = new CandidateApplicationAdapter('Application','CandidateApplication',{"candidate":data.id}
     *  modJs.subModJsList['tabCandidateApplication'].setRemoteTable(true);
     */

  setRemoteTable(val) {
    this.createRemoteTable = val;
  }

  setSettings(val) {
    this.settings = val;
  }

  getRemoteTable() {
    return this.createRemoteTable;
  }

  isAllLoaded(fieldMasterDataKeys) {
    for (const key in fieldMasterDataKeys) {
      if (fieldMasterDataKeys[key] === false) {
        return false;
      }
    }
    return true;
  }

  // eslint-disable-next-line no-unused-vars
  initFieldMasterDataResponse(key, data, callback, loadAllCallbackData) {
    this.fieldMasterData[key] = data;
    this.fieldMasterDataKeys[key] = true;

    if (callback !== undefined && callback !== null) {
      callback();
    }

    if (this.fieldMasterDataCallback !== null
            && this.fieldMasterDataCallback !== undefined
            && this.isAllLoaded(this.fieldMasterDataKeys)
            && (this.fieldMasterDataCallbackData !== null && this.fieldMasterDataCallbackData !== undefined)
    ) {
      this.fieldMasterDataCallback(this.fieldMasterDataCallbackData);
    } else if (this.fieldMasterDataCallback !== null
            && this.fieldMasterDataCallback !== undefined
            && this.isAllLoaded(this.fieldMasterDataKeys)
    ) {
      this.fieldMasterDataCallback();
    }
  }

  getMetaFieldValues(key, fields) {
    for (let i = 0; i < fields.length; i++) {
      if (key === fields[i][0]) {
        return fields[i][1];
      }
    }
    return null;
  }

  getThemeColors() {
    const colors = ['red', 'yellow', 'aqua', 'blue',
      'light-blue', 'green', 'navy', 'teal', 'olive', 'orange',
      'fuchsia', 'purple'];

    return colors;
  }

  getColorByRandomString(string) {
    const colors = this.getThemeColors();
    const k = string.charCodeAt(0);
    return colors[k % colors.length];
  }

  getColorByFileType(type) {
    type = type.toLowerCase();

    const colorMap = {};
    colorMap.pdf = 'red';
    colorMap.csv = 'yellow';
    colorMap.xls = 'green';
    colorMap.xlsx = 'green';
    colorMap.doc = 'light-blue';
    colorMap.docx = 'light-blue';
    colorMap.docx = 'blue';
    colorMap.ppt = 'orange';
    colorMap.pptx = 'orange';
    colorMap.jpg = 'teal';
    colorMap.jpeg = 'teal';
    colorMap.gif = 'green';
    colorMap.png = 'yellow';
    colorMap.bmp = 'fuchsia';


    if (colorMap[type] !== undefined || colorMap[type] != null) {
      return colorMap[type];
    }
    return this.getColorByRandomString(type);
  }

  getIconByFileType(type) {
    type = type.toLowerCase();

    const iconMap = {};
    iconMap.pdf = 'far fa-file-pdf';
    iconMap.csv = 'fa far fa-file-code';
    iconMap.xls = 'far fa-file-excel';
    iconMap.xlsx = 'far fa-file-excel';
    iconMap.doc = 'far fa-file-word';
    iconMap.docx = 'far fa-file-word';
    iconMap.ppt = 'far fa-file-powerpoint';
    iconMap.pptx = 'far fa-file-powerpoint';
    iconMap.jpg = 'far fa-file-image';
    iconMap.jpeg = 'far fa-file-image';
    iconMap.gif = 'far fa-file-image';
    iconMap.png = 'far fa-file-image';
    iconMap.bmp = 'far fa-file-image';
    iconMap.txt = 'far fa-file-text';
    iconMap.rtf = 'far fa-file-text';


    if (iconMap[type] !== undefined || iconMap[type] != null) {
      return iconMap[type];
    }
    return 'far fa-file';
  }

  getSourceMapping() {
    return this.sourceMapping;
  }

  setTesting(testing) {
    this.testing = testing;
  }

  consoleLog(message) {
    if (this.testing) {
      console.log(message);
    }
  }

  setClientMessages(msgList) {
    this.msgList = msgList;
  }

  setTemplates(templates) {
    this.templates = templates;
  }


  getWSProperty(array, key) {
    if (array.hasOwnProperty(key)) {
      return array[key];
    }
    return null;
  }


  getClientMessage(key) {
    return this.getWSProperty(this.msgList, key);
  }


  getTemplate(key) {
    return this.getWSProperty(this.templates, key);
  }

  setGoogleAnalytics(gaq) {
    this.gaq = gaq;
  }


  showView(view) {
    if (this.currentView != null) {
      this.previousView = this.currentView;
      $(`#${this.currentView}`).hide();
    }
    $(`#${view}`).show();
    this.currentView = view;
    this.moveToTop();
  }

  showPreviousView() {
    this.showView(this.previousView);
  }


  moveToTop() {

  }


  callFunction(callback, cbParams, thisParam) {
    if ($.isFunction(callback)) {
      try {
        if (thisParam === undefined || thisParam === null) {
          callback.apply(document, cbParams);
        } else {
          callback.apply(thisParam, cbParams);
        }
      } catch (e) {
        console.log(e.message);
      }
    } else {
      const f = this[callback];
      if ($.isFunction(f)) {
        try {
          f.apply(this, cbParams);
        } catch (e) {
          console.log(e.message);
        }
      }
    }
  }

  getTableTopButtonHtml() {
    let html = '';
    if (this.getShowAddNew()) {
      html = `<button onclick="modJs.renderForm();return false;" class="btn btn-small btn-primary">${this.gt(this.getAddNewLabel())} <i class="fa fa-plus"></i></button>`;
    }

    if (this.getFilters() != null) {
      if (html !== '') {
        html += '&nbsp;&nbsp;';
      }
      html += `<button onclick="modJs.showFilters();return false;" class="btn btn-small btn-primary">${this.gt('Filter')} <i class="fa fa-filter"></i></button>`;
      html += '&nbsp;&nbsp;';
      if (this.filtersAlreadySet) {
        html += '<button id="__id___resetFilters" onclick="modJs.resetFilters();return false;" class="btn btn-small btn-default">__filterString__ <i class="fa fa-times"></i></button>';
      } else {
        html += '<button id="__id___resetFilters" onclick="modJs.resetFilters();return false;" class="btn btn-small btn-default" style="display:none;">__filterString__ <i class="fa fa-times"></i></button>';
      }
    }

    html = html.replace(/__id__/g, this.getTableName());

    if (this.currentFilterString !== '' && this.currentFilterString != null) {
      html = html.replace(/__filterString__/g, this.currentFilterString);
    } else {
      html = html.replace(/__filterString__/g, 'Reset Filters');
    }

    if (html !== '') {
      html = `<div class="row"><div class="col-xs-12">${html}</div></div>`;
    }

    return html;
  }


  getActionButtonHeader() {
    return { sTitle: '', sClass: 'center' };
  }

  getTableHTMLTemplate() {
    return '<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>';
  }

  isSortable() {
    return true;
  }

  /**
     * Create the data table on provided element id
     * @method createTable
     * @param val {Boolean}
     */

  createTable(elementId) {
    const that = this;

    if (this.getRemoteTable()) {
      this.createTableServer(elementId);
      return;
    }


    const headers = this.getHeaders();

    // add translations
    for (const index in headers) {
      headers[index].sTitle = this.gt(headers[index].sTitle);
    }

    const data = this.getTableData();

    if (this.showActionButtons()) {
      headers.push(this.getActionButtonHeader());
    }


    if (this.showActionButtons()) {
      for (let i = 0; i < data.length; i++) {
        data[i].push(this.getActionButtonsHtml(data[i][0], data[i]));
      }
    }

    let html = '';
    html = this.getTableTopButtonHtml() + this.getTableHTMLTemplate();
    /*
         if(this.getShowAddNew()){
         html = this.getTableTopButtonHtml()+'<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>';
         }else{
         html = '<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>';
         }
         */
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
      bSort: that.isSortable(),
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
    $('.tableActionButton').tooltip();
  }

  getTableSize( ) {
    return 15;
  }

  /**
     * Create a data table on provided element id which loads data page by page
     * @method createTableServer
     * @param val {Boolean}
     */

  createTableServer(elementId) {
    const that = this;
    const headers = this.getHeaders();

    headers.push({ sTitle: '', sClass: 'center' });

    // add translations
    for (const index in headers) {
      headers[index].sTitle = this.gt(headers[index].sTitle);
    }

    let html = '';
    html = this.getTableTopButtonHtml() + this.getTableHTMLTemplate();

    // Find current page
    const activePage = $(`#${elementId} .dataTables_paginate .active a`).html();
    let start = 0;
    if (activePage !== undefined && activePage != null) {
      start = parseInt(activePage, 10) * this.getTableSize() - this.getTableSize();
    }


    $(`#${elementId}`).html(html);

    const dataTableParams = {
      oLanguage: {
        sLengthMenu: '_MENU_ records per page',
      },
      bProcessing: true,
      bServerSide: true,
      sAjaxSource: that.getDataUrl(that.getDataMapping()),
      aoColumns: headers,
      bSort: that.isSortable(),
      parent: that,
      iDisplayLength: this.getTableSize(),
      iDisplayStart: start,
    };

    if (this.showActionButtons()) {
      dataTableParams.aoColumnDefs = [
        {
          fnRender: that.getActionButtons,
          aTargets: [that.getDataMapping().length],
        },
      ];
    }

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

    $('.tableActionButton').tooltip();
  }

  /**
     * This should be overridden in module lib.js classes to return module headers which are used to create the data table.
     * @method getHeaders
     * @example
     SettingAdapter.method('getHeaders() {
      return [
      { "sTitle": "ID" ,"bVisible":false},
      { "sTitle": "Name" },
      { "sTitle": "Value"},
      { "sTitle": "Details"}
    ];
    }
     */
  getHeaders() {

  }


  /**
     * This should be overridden in module lib.js classes to return module field values which are used to create the data table.
     * @method getDataMapping
     * @example
     SettingAdapter.method('getDataMapping() {
  return [
          "id",
          "name",
          "value",
          "description"
  ];
  }
     */

  getDataMapping() {

  }

  /**
     * This should be overridden in module lib.js classes to return module from fields which are used to create the add/edit form and also used for initializing select box values in form.
     * @method getFormFields
     * @example
     SettingAdapter.method('getFormFields() {
  return [
          [ "id", {"label":"ID","type":"hidden"}],
          [ "value", {"label":"Value","type":"text","validation":"none"}]
  ];
  }
     */
  getFormFields() {

  }

  getTableColumns() {
    return [];
  }

  getTableData() {

  }

  /**
     * This can be overridden in module lib.js classes inorder to show a filter form
     * @method getFilters
     * @example
     EmployeeAdapter.method('getFilters() {
    return [
            [ "job_title", {"label":"Job Title","type":"select2","allow-null":true,"null-label":"All Job Titles","remote-source":["JobTitle","id","name"]}],
            [ "department", {"label":"Department","type":"select2","allow-null":true,"null-label":"All Departments","remote-source":["CompanyStructure","id","title"]}],
            [ "supervisor", {"label":"Supervisor","type":"select2","allow-null":true,"null-label":"Anyone","remote-source":["Employee","id","first_name+last_name"]}]
    ];
  }
     */
  getFilters() {
    return null;
  }

  getAdditionalRemoteFields() {
    return null;
  }

  /**
     * Show the edit form for an item
     * @method edit
     * @param id {int} id of the item to edit
     */
  edit(id) {
    this.currentId = id;
    this.getElement(id, []);
  }

  copyRow(id) {
    this.getElement(id, [], true);
  }

  renderModel(id, header, body) {
    $(`#${id}ModelBody`).html('');

    if (body === undefined || body == null) {
      body = '';
    }

    $(`#${id}ModelLabel`).html(header);
    $(`#${id}ModelBody`).html(body);
  }


  renderYesNoModel(header, body, yesBtnName, noBtnName, callback, callbackParams) {
    const that = this;
    const modelId = '#yesnoModel';

    if (body === undefined || body == null) {
      body = '';
    }

    $(`${modelId}Label`).html(header);
    $(`${modelId}Body`).html(body);
    if (yesBtnName != null) {
      $(`${modelId}YesBtn`).html(yesBtnName);
    }
    if (noBtnName != null) {
      $(`${modelId}NoBtn`).html(noBtnName);
    }

    $(`${modelId}YesBtn`).off().on('click', () => {
      if (callback !== undefined && callback != null) {
        callback.apply(that, callbackParams);
        that.cancelYesno();
      }
    });

    $(modelId).modal({
      backdrop: 'static',
    });
  }

  renderModelFromDom(id, header, element) {
    $(`#${id}ModelBody`).html('');

    if (element === undefined || element == null) {
      element = $('<div></div>');
    }

    $(`#${id}ModelLabel`).html(header);
    $(`#${id}ModelBody`).html('');
    $(`#${id}ModelBody`).append(element);
  }

  /**
     * Delete an item
     * @method deleteRow
     * @param id {int} id of the item to edit
     */

  deleteRow(id) {
    this.deleteParams.id = id;
    this.renderModel('delete', 'Confirm Deletion', 'Are you sure you want to delete this item ?');
    $('#deleteModel').modal('show');
  }

  /**
     * Show a popup with message
     * @method showMessage
     * @param title {String} title of the message box
     * @param message {String} message
     * @param closeCallback {Function} this will be called once the dialog is closed (optional)
     * @param closeCallback {Function} data to pass to close callback (optional)
     * @param closeCallbackData
     * @param isPlain {Boolean} if true buttons are not shown (optional / default = true)
     * @example
     *   this.showMessage("Error Occured while Applying Leave", callBackData);
     */
  showMessage(title, message, closeCallback = null, closeCallbackData = null, isPlain = false) {
    const that = this;
    let modelId = '';
    if (isPlain) {
      modelId = '#plainMessageModel';
    } else {
      modelId = '#messageModel';
    }

    $(modelId).off();

    if (isPlain) {
      this.renderModel('plainMessage', title, message);
    } else {
      this.renderModel('message', title, message);
    }

    if (closeCallback !== null && closeCallback !== undefined) {
      $(modelId).modal({ show: true });
      $(modelId).on('hidden.bs.modal', () => {
        closeCallback.apply(that, closeCallbackData);
        $('.modal-backdrop').remove();
      });
    } else {
      $(modelId).modal({
        backdrop: 'static',
      });
    }
  }

  showDomElement(title, element, closeCallback, closeCallbackData, isPlain) {
    const that = this;
    let modelId = '';
    if (isPlain) {
      modelId = '#dataMessageModel';
    } else {
      modelId = '#messageModel';
    }

    $(modelId).unbind('hide');

    if (isPlain) {
      this.renderModelFromDom('dataMessage', title, element);
    } else {
      this.renderModelFromDom('message', title, element);
    }


    if (closeCallback !== null && closeCallback !== undefined) {
      $(modelId).modal({ show: true });
      $(modelId).on('hidden.bs.modal', () => {
        closeCallback.apply(that, closeCallbackData);
        $('.modal-backdrop').remove();
      });
    } else {
      $(modelId).modal({
        backdrop: 'static',
      });
    }
  }

  confirmDelete() {
    if (this.deleteParams.id !== undefined || this.deleteParams.id != null) {
      this.deleteObj(this.deleteParams.id, []);
    }
    $('#deleteModel').modal('hide');
  }

  cancelDelete() {
    $('#deleteModel').modal('hide');
    this.deleteParams.id = null;
  }

  closeMessage() {
    $('#messageModel').modal('hide');
  }

  cancelYesno() {
    $('#yesnoModel').modal('hide');
  }

  closeModal(id) {
    $(id).modal('hide');
  }

  closePlainMessage() {
    $('#plainMessageModel').modal('hide');
    $('#dataMessageModel').modal('hide');
  }

  closeDataMessage() {
    $('#dataMessageModel').modal('hide');
  }


  /**
     * Create or edit an element
     * @method save
     * @param getFunctionCallBackData {Array} once a success is returned call get() function for this module with these parameters
     * @param successCallback {Function} this will get called after success response
     */

  save(callGetFunction, successCallback) {
    const validator = new FormValidation(`${this.getTableName()}_submit`, true, { ShowPopup: false, LabelErrorClass: 'error' });
    if (validator.checkValues()) {
      let params = validator.getFormParameters();
      params = this.forceInjectValuesBeforeSave(params);
      const msg = this.doCustomValidation(params);
      if (msg == null) {
        if (this.csrfRequired) {
          params.csrf = $(`#${this.getTableName()}Form`).data('csrf');
        }
        const id = $(`#${this.getTableName()}_submit #id`).val();
        if (id != null && id !== undefined && id !== '') {
          params.id = id;
        }
        params = this.makeEmptyDateFieldsNull(params);
        this.add(params, [], callGetFunction, successCallback);
      } else {
        $(`#${this.getTableName()}Form .label`).html(msg);
        $(`#${this.getTableName()}Form .label`).show();
        this.scrollToTop();
      }
    }
  }


  makeEmptyDateFieldsNull(params) {
    const fields = this.getFormFields();
    fields.forEach((field) => {
      if ((field[1].type === 'date' || field[1].type === 'datetime')
                && (params[field[0]] === '' || params[field[0]] === '0000-00-00' || params[field[0]] === '0000-00-00 00:00:00')) {
        if (field[1].validation === 'none') {
          params[field[0]] = 'NULL';
        } else {
          delete params[field[0]];
        }
      }
    });
    return params;
  }

  validatePassword(password) {
    if (password.length < 8) {
      return this.gt('Password too short');
    }

    if (password.length > 30) {
      return this.gt('Password too long');
    }

    const numberTester = /.*[0-9]+.*$/;
    if (!password.match(numberTester)) {
      return this.gt('Password must include at least one number');
    }

    const lowerTester = /.*[a-z]+.*$/;
    if (!password.match(lowerTester)) {
      return this.gt('Password must include at least one lowercase letter');
    }

    const upperTester = /.*[A-Z]+.*$/;
    if (!password.match(upperTester)) {
      return this.gt('Password must include at least one uppercase letter');
    }

    const symbolTester = /.*[\W]+.*$/;
    if (!password.match(symbolTester)) {
      return this.gt('Password must include at least one symbol');
    }

    return null;
  }

  /**
     * Override this method to inject attitional parameters or modify existing parameters retrived from
     * add/edit form before sending to the server
     * @method forceInjectValuesBeforeSave
     * @param params {Array} keys and values in form
     * @returns {Array} modified parameters
     */
  forceInjectValuesBeforeSave(params) {
    return params;
  }

  /**
     * Override this method to do custom validations at client side
     * @method doCustomValidation
     * @param params {Array} keys and values in form
     * @returns {Null or String} return null if validation success, returns error message if unsuccessful
     * @example
     EmployeeLeaveAdapter.method('doCustomValidation(params) {
    try{
      if(params['date_start'] != params['date_end']){
        var ds = new Date(params['date_start']);
        var de = new Date(params['date_end']);
        if(de < ds){
          return "Start date should be earlier than end date of the leave period";
        }
      }
    }catch(e){

    }
  return null;
}
     */
  // eslint-disable-next-line no-unused-vars
  doCustomValidation(params) {
    return null;
  }

  filterQuery() {
    const validator = new FormValidation(`${this.getTableName()}_filter`, true, { ShowPopup: false, LabelErrorClass: 'error' });
    if (validator.checkValues()) {
      const params = validator.getFormParameters();
      if (this.doCustomFilterValidation(params)) {
        // remove null params
        for (const prop in params) {
          if (params.hasOwnProperty(prop)) {
            if (params[prop] === 'NULL') {
              delete (params[prop]);
            }
          }
        }

        this.setFilter(params);
        this.filtersAlreadySet = true;
        $(`#${this.getTableName()}_resetFilters`).show();
        this.currentFilterString = this.getFilterString(params);

        this.get([]);
        this.closePlainMessage();
      }
    }
  }


  getFilterString(filters) {
    let str = '';
    let rmf; let source; let values; let select2MVal; let value; let
      valueOrig;

    const filterFields = this.getFilters();


    if (values == null) {
      values = [];
    }

    for (const prop in filters) {
      if (filters.hasOwnProperty(prop)) {
        values = this.getMetaFieldValues(prop, filterFields);
        if (!values) {
          continue;
        }
        value = '';
        valueOrig = null;

        if ((values.type === 'select' || values.type === 'select2')) {
          if (values['remote-source'] !== undefined && values['remote-source'] != null) {
            rmf = values['remote-source'];
            if (filters[prop] === 'NULL') {
              if (values['null-label'] !== undefined && values['null-label'] != null) {
                value = values['null-label'];
              } else {
                value = 'Not Selected';
              }
            } else {
              let key = `${rmf[0]}_${rmf[1]}_${rmf[2]}`;
              if (rmf.length > 3) {
                key = `${key}_${rmf[3]}`;
              }
              // value = this.fieldMasterData[`${rmf[0]}_${rmf[1]}_${rmf[2]}`][filters[prop]];
              value = this.fieldMasterData[key][filters[prop]];
              valueOrig = value;
            }
          } else {
            source = values.source[0];
            if (filters[prop] === 'NULL') {
              if (values['null-label'] !== undefined && values['null-label'] != null) {
                value = values['null-label'];
              } else {
                value = 'Not Selected';
              }
            } else {
              for (let i = 0; i < source.length; i++) {
                if (filters[prop] === values.source[i][0]) {
                  value = values.source[i][1];
                  valueOrig = value;
                  break;
                }
              }
            }
          }
        } else if (values.type === 'select2multi') {
          select2MVal = [];
          try {
            select2MVal = JSON.parse(filters[prop]);
          } catch (e) {
            // Do nothing
          }

          value = select2MVal.join(',');
          if (value !== '') {
            valueOrig = value;
          }
        } else {
          value = filters[prop];
          if (value !== '') {
            valueOrig = value;
          }
        }

        if (valueOrig != null) {
          if (str !== '') {
            str += ' | ';
          }

          str += `${values.label} = ${value}`;
        }
      }
    }

    return str;
  }

  /**
     * Override this method to do custom validations at client side for values selected in filters
     * @method doCustomFilterValidation
     * @param params {Array} keys and values in form
     * @returns {Null or String} return null if validation success, returns error message if unsuccessful
     */
  doCustomFilterValidation(params) {
    return true;
  }


  /**
     * Reset selected filters
     * @method resetFilters
     */

  resetFilters() {
    this.filter = this.origFilter;
    this.filtersAlreadySet = false;
    $(`#${this.getTableName()}_resetFilters`).hide();
    this.currentFilterString = '';
    this.get([]);
  }

  redirectToUrl(url) {
    top.location.href = url;
  }


  showFilters(object) {
    let formHtml = this.templates.filterTemplate;
    let html = '';
    const fields = this.getFilters();

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
    formHtml = formHtml.replace(/_id_/g, `${this.getTableName()}_filter`);
    formHtml = formHtml.replace(/_fields_/g, html);

    const randomFormId = this.generateRandom(14);
    const $tempDomObj = $('<div class="reviewBlock popupForm" data-content="Form"></div>');
    $tempDomObj.attr('id', randomFormId);

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
    tinymce.init({
      selector: `#${$tempDomObj.attr('id')} .tinymce`,
      height: '400',
    });

    $tempDomObj.find('.simplemde').each(function () {
      const simplemde = new SimpleMDE({ element: $(this)[0] });
      $(this).data('simplemde', simplemde);
      // simplemde.value($(this).val());
    });

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

    /*
         $tempDomObj.find('.signatureField').each(function() {
         $(this).data('signaturePad',new SignaturePad($(this)));
         });
         */

    // var tHtml = $tempDomObj.wrap('<div>').parent().html();
    this.showDomElement('Edit', $tempDomObj, null, null, true);
    $('.filterBtn').off();
    $('.filterBtn').on('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      try {
        modJs.filterQuery();
      } catch (err) {
        console.log(err);
        console.log(err.message);
      }
      return false;
    });

    if (this.filter !== undefined && this.filter != null && this.filter !== '') {
      this.fillForm(this.filter, `#${this.getTableName()}_filter`, this.getFilters());
    }
  }


  /**
     * Override this method in your module class to make changes to data fo the form before showing the form
     * @method preRenderForm
     * @param object {Array} keys value list for populating form
     */

  preRenderForm(object) {

  }

  /**
     * Create the form
     * @method renderForm
     * @param object {Array} keys value list for populating form
     */

  renderForm(object) {
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

    tinymce.init({
      selector: `#${$tempDomObj.attr('id')} .tinymce`,
      height: '400',
    });

    $tempDomObj.find('.simplemde').each(function () {
      const simplemde = new SimpleMDE({ element: $(this)[0] });
      $(this).data('simplemde', simplemde);
      // simplemde.value($(this).val());
    });

    const codeMirror = this.codeMirror;
    if (codeMirror) {
      $tempDomObj.find('.code').each(function () {
        const editor = codeMirror.fromTextArea($(this)[0], {
          lineNumbers: false,
          matchBrackets: true,
          continueComments: 'Enter',
          extraKeys: { 'Ctrl-Q': 'toggleComment' },
        });
        $(this).data('codemirror', editor);
      });
    }


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

    // Input mask
    $tempDomObj.find('[mask]').each(function () {
      $(this).inputmask($(this).attr('mask'));
    });

    $tempDomObj.find('[datemask]').each(function () {
      $(this).inputmask({
        mask: 'y-1-2',
        placeholder: 'YYYY-MM-DD',
        leapday: '-02-29',
        separator: '-',
        alias: 'yyyy/mm/dd',
      });
    });

    $tempDomObj.find('[datetimemask]').each(function () {
      $(this).inputmask('datetime', {
        mask: 'y-2-1 h:s:00',
        placeholder: 'YYYY-MM-DD hh:mm:ss',
        leapday: '-02-29',
        separator: '-',
        alias: 'yyyy/mm/dd',
      });
    });

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
      } else {
        this.setDefaultValues();
      }

      this.scrollToTop();
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
      } else {
        this.setDefaultValues(`#${randomFormId}`);
      }
    }

    this.postRenderForm(object, $tempDomObj);
  }

  setDefaultValues(formId, fields) {
    if (fields == null || fields === undefined) {
      fields = this.getFormFields();
    }

    if (formId == null || formId === undefined || formId === '') {
      formId = `#${this.getTableName()}Form`;
    }


    for (let i = 0; i < fields.length; i++) {
      if (fields[i][1].type !== 'text' && fields[i][1].type !== 'textarea') {
        continue;
      }

      if (fields[i][1].default !== undefined && fields[i][1].default !== null) {
        $(`${formId} #${fields[i][0]}`).val(fields[i][1].default);
      }
    }
  }

  retriveItemsAfterSave() {
    return true;
  }

  /**
     * Override this method in your module class to make changes to data fo the form after showing it
     * @method postRenderForm
     * @param object {Array} keys value list for populating form
     * @param $tempDomObj {DOM} a DOM element for the form
     * @example
     *   UserAdapter.method('postRenderForm(object, $tempDomObj) {
    if(object == null || object == undefined){
      $tempDomObj.find("#changePasswordBtn").remove();
    }
  }
     */

  postRenderForm(object, $tempDomObj) {

  }

  /**
     * Convert data group field to HTML
     * @method dataGroupToHtml
     * @param val {String} value in the field
     * @param field {Array} field meta data
     */

  dataGroupToHtml(val, field) {
    const data = JSON.parse(val);


    let t; let sortFunction; let item; let itemHtml; let itemVal;

    const deleteButton = '<a id="#_id_#_delete" onclick="modJs.deleteDataGroupItem(\'#_id_#\');return false;" type="button" style="float:right;margin-right:3px;" tooltip="Delete"><li class="fa fa-times"></li></a>';
    const editButton = '<a id="#_id_#_edit" onclick="modJs.editDataGroupItem(\'#_id_#\');return false;" type="button" style="float:right;margin-right:5px;" tooltip="Edit"><li class="fa fa-edit"></li></a>';

    const template = field[1].html;

    if (data != null && data !== undefined && field[1]['sort-function'] !== undefined && field[1]['sort-function'] != null) {
      data.sort(field[1]['sort-function']);
    }


    const html = $(`<div id="${field[0]}_div_inner"></div>`);


    for (let i = 0; i < data.length; i++) {
      item = data[i];

      if (field[1]['pre-format-function'] !== undefined && field[1]['pre-format-function'] != null) {
        item = field[1]['pre-format-function'].apply(this, [item]);
      }

      t = template;
      t = t.replace('#_delete_#', deleteButton);
      t = t.replace('#_edit_#', editButton);
      t = t.replace(/#_id_#/g, item.id);

      for (const key in item) {
        itemVal = item[key];
        if (itemVal !== undefined && itemVal != null && typeof itemVal === 'string') {
          itemVal = itemVal.replace(/(?:\r\n|\r|\n)/g, '<br />');
        }
        t = t.replace(`#_${key}_#`, itemVal);
      }

      if (field[1].render !== undefined && field[1].render != null) {
        t = t.replace('#_renderFunction_#', field[1].render(item));
      }

      itemHtml = $(t);
      itemHtml.attr('fieldId', `${field[0]}_div`);
      html.append(itemHtml);
    }


    return html;
  }

  /**
     * Reset the DataGroup for a given field
     * @method resetDataGroup
     * @param field {Array} field meta data
     */
  resetDataGroup(field) {
    $(`#${field[0]}`).val('');
    $(`#${field[0]}_div`).html('');
  }

  showDataGroup(field, object, callback) {
    let formHtml = this.templates.datagroupTemplate;
    let html = '';
    const fields = field[1].form;

    if (object !== undefined && object != null && object.id !== undefined) {
      this.currentDataGroupItemId = object.id;
    } else {
      this.currentDataGroupItemId = null;
    }

    for (let i = 0; i < fields.length; i++) {
      html += this.renderFormField(fields[i]);
    }
    formHtml = formHtml.replace(/_id_/g, `${this.getTableName()}_field_${field[0]}`);
    formHtml = formHtml.replace(/_fields_/g, html);

    const randomFormId = this.generateRandom(14);
    const $tempDomObj = $('<div class="reviewBlock popupForm" data-content="Form"></div>');
    $tempDomObj.attr('id', randomFormId);

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

    tinymce.init({
      selector: `#${$tempDomObj.attr('id')} .tinymce`,
      height: '400',
    });

    $tempDomObj.find('.simplemde').each(function () {
      const simplemde = new SimpleMDE({ element: $(this)[0] });
      $(this).data('simplemde', simplemde);
      // simplemde.value($(this).val());
    });

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


    this.currentDataGroupField = field;
    this.showDomElement(`Add ${field[1].label}`, $tempDomObj, null, null, true);

    if (object !== undefined && object != null) {
      this.fillForm(object, `#${this.getTableName()}_field_${field[0]}`, field[1].form);
    } else {
      this.setDefaultValues(`#${this.getTableName()}_field_${field[0]}`, field[1].form);
    }


    $('.groupAddBtn').off();
    if (object !== undefined && object != null && object.id !== undefined) {
      $('.groupAddBtn').on('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        try {
          modJs.editDataGroup(callback);
        } catch (err) {
          console.log(`Error editing data group: ${err.message}`);
        }
        return false;
      });
    } else {
      $('.groupAddBtn').on('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        try {
          modJs.addDataGroup(callback);
        } catch (err) {
          console.log(`Error adding data group: ${err.message}`);
        }
        return false;
      });
    }
  }

  addDataGroup(callback, existingData) {
    const field = this.currentDataGroupField;
    let tempParams;
    $(`#${this.getTableName()}_field_${field[0]}_error`).html('');
    $(`#${this.getTableName()}_field_${field[0]}_error`).hide();
    const validator = new FormValidation(`${this.getTableName()}_field_${field[0]}`, true, { ShowPopup: false, LabelErrorClass: 'error' });
    if (validator.checkValues()) {
      let params = validator.getFormParameters();
      if (field[1]['custom-validate-function'] !== undefined && field[1]['custom-validate-function'] != null) {
        tempParams = field[1]['custom-validate-function'].apply(this, [params]);
        if (tempParams.valid) {
          params = tempParams.params;
        } else {
          $(`#${this.getTableName()}_field_${field[0]}_error`).html(tempParams.message);
          $(`#${this.getTableName()}_field_${field[0]}_error`).show();
          return false;
        }
      }
      let val = '[]';
      if (existingData) {
        val = existingData;
      } else {
        val = $(`#${field[0]}`).val();
        if (val === '' || val == null) {
          val = '[]';
        }
      }
      const data = JSON.parse(val);

      params.id = `${field[0]}_${this.dataGroupGetNextAutoIncrementId(data)}`;
      data.push(params);


      if (field[1]['sort-function'] !== undefined && field[1]['sort-function'] != null) {
        data.sort(field[1]['sort-function']);
      }

      val = JSON.stringify(data);

      const html = this.dataGroupToHtml(val, field);
      if (callback) {
        callback(val);
      }

      $(`#${field[0]}_div`).html('');
      $(`#${field[0]}_div`).append(html);

      this.makeDataGroupSortable(field, $(`#${field[0]}_div_inner`));


      $(`#${field[0]}`).val(val);
      this.orderDataGroup(field);

      this.closeDataMessage();

      this.showMessage('Item Added', 'This change will be effective only when you save the form');
    }
    return true;
  }

  nl2br(str, len) {
    let t = '';
    try {
      const arr = str.split(' ');
      let count = 0;
      for (let i = 0; i < arr.length; i++) {
        count += arr[i].length + 1;
        if (count > len) {
          t += `${arr[i]}<br/>`;
          count = 0;
        } else {
          t += `${arr[i]} `;
        }
      }
    } catch (e) {
      // Do nothing
    }
    return t;
  }

  makeDataGroupSortable(field, obj) {
    obj.data('field', field);
    obj.data('firstSort', true);
    obj.sortable({

      create() {
        $(this).height($(this).height());
      },

      'ui-floating': false,
      start(e, uiStart) {
        $('#sortable-ul-selector-id').sortable({
          sort(event, ui) {
            const $target = $(event.target);
            if (!/html|body/i.test($target.offsetParent()[0].tagName)) {
              const top = event.pageY - $target.offsetParent().offset().top - (ui.helper.outerHeight(true) / 2);
              ui.helper.css({ top: `${top}px` });
            }
          },
        });
      },
      revert: true,
      stop() {
        modJs.orderDataGroup($(this).data('field'));
      },
      axis: 'y',
      scroll: false,
      placeholder: 'sortable-placeholder',
      cursor: 'move',
    });
  }

  orderDataGroup(field, callback) {
    const newArr = []; let
      id;
    const list = $(`#${field[0]}_div_inner [fieldid='${field[0]}_div']`);
    let val = $(`#${field[0]}`).val();
    if (val === '' || val == null) {
      val = '[]';
    }
    const data = JSON.parse(val);
    list.each(function () {
      id = $(this).attr('id');
      for (const index in data) {
        if (data[index].id === id) {
          newArr.push(data[index]);
          break;
        }
      }
    });

    $(`#${field[0]}`).val(JSON.stringify(newArr));

    if (callback != null) {
      callback(newArr);
    }
  }


  editDataGroup(callback, existingData) {
    const field = this.currentDataGroupField;
    const id = this.currentDataGroupItemId;
    const validator = new FormValidation(`${this.getTableName()}_field_${field[0]}`, true, { ShowPopup: false, LabelErrorClass: 'error' });
    if (validator.checkValues()) {
      let params = validator.getFormParameters();

      if (field[1]['custom-validate-function'] !== undefined && field[1]['custom-validate-function'] != null) {
        const tempParams = field[1]['custom-validate-function'].apply(this, [params]);
        if (tempParams.valid) {
          params = tempParams.params;
        } else {
          $(`#${this.getTableName()}_field_${field[0]}_error`).html(tempParams.message);
          $(`#${this.getTableName()}_field_${field[0]}_error`).show();
          return false;
        }
      }


      if (this.doCustomFilterValidation(params)) {
        let val = '[]';
        if (existingData) {
          val = existingData;
        } else {
          val = $(`#${field[0]}`).val();
          if (val === '' || val == null) {
            val = '[]';
          }
        }
        const data = JSON.parse(val);

        let editVal = {};
        let editValIndex = -1;
        const newVals = [];
        for (let i = 0; i < data.length; i++) {
          const item = data[i];
          if (item.id === id) {
            editVal = item;
            editValIndex = i;
          }
          newVals.push(item);
        }


        params.id = editVal.id;
        newVals[editValIndex] = params;

        if (field[1]['sort-function'] !== undefined && field[1]['sort-function'] != null) {
          newVals.sort(field[1]['sort-function']);
        }

        val = JSON.stringify(newVals);
        $(`#${field[0]}`).val(val);

        const html = this.dataGroupToHtml(val, field);

        if (callback) {
          callback(newVals);
        }

        this.orderDataGroup(field);

        $(`#${field[0]}_div`).html('');
        $(`#${field[0]}_div`).append(html);

        this.makeDataGroupSortable(field, $(`#${field[0]}_div_inner`));


        this.closeDataMessage();

        this.showMessage('Item Edited', 'This change will be effective only when you save the form');
      }
    }

    return true;
  }

  editDataGroupItem(id, existingData, field) {
    const fieldId = id.substring(0, id.lastIndexOf('_'));

    let val;
    if (existingData) {
      val = decodeURI(existingData);
    } else {
      val = $(`#${fieldId}`).val();
    }
    const data = JSON.parse(val);

    let editVal = {};

    for (let i = 0; i < data.length; i++) {
      const item = data[i];
      if (item.id === id) {
        editVal = item;
      }
    }

    if (field) {
      field = JSON.parse(decodeURI(field));
    } else {
      field = $(`#${fieldId}`).data('field');
    }

    this.showDataGroup(field, editVal);
  }

  dataGroupGetNextAutoIncrementId(data) {
    let autoId = 1; let
      id;
    for (let i = 0; i < data.length; i++) {
      const item = data[i];
      if (item.id === undefined || item.id == null) {
        item.id = 1;
      }
      id = item.id.substring(item.id.lastIndexOf('_') + 1, item.id.length);
      if (id >= autoId) {
        autoId = parseInt(id, 10) + 1;
      }
    }

    return autoId;
  }


  deleteDataGroupItem(id, existingData) {
    const fieldId = id.substring(0, id.lastIndexOf('_'));

    let val;
    if (existingData) {
      val = decodeURI(existingData);
    } else {
      val = $(`#${fieldId}`).val();
    }
    const data = JSON.parse(val);

    const newVal = [];

    for (let i = 0; i < data.length; i++) {
      const item = data[i];
      if (item.id !== id) {
        newVal.push(item);
      }
    }

    $(`#${fieldId}`).val(JSON.stringify(newVal));

    $(`#${id}`).remove();

    this.showMessage('Item Removed', 'Item removed. This change will be effective only when you save the form');
  }


  /**
     * Fill a form with required values after showing it
     * @method fillForm
     * @param object {Array} form data
     * @param formId {String} id of the form
     * @param formId {Array} field meta data
     */

  fillForm(object, formId, fields) {
    let placeHolderVal;
    if (fields == null || fields === undefined) {
      fields = this.getFormFields();
    }

    if (formId == null || formId === undefined || formId === '') {
      formId = `#${this.getTableName()}Form`;
    }


    for (let i = 0; i < fields.length; i++) {
      if (fields[i][1].type === 'date') {
        if (object[fields[i][0]] !== '0000-00-00' && object[fields[i][0]] !== '' && object[fields[i][0]] != null && object[fields[i][0]] !== undefined) {
          $(`${formId} #${fields[i][0]}_date`).datepicker('setValue', object[fields[i][0]]);
        }
      } else if (fields[i][1].type === 'colorpick') {
        if (object[fields[i][0]] != null && object[fields[i][0]] !== undefined) {
          $(`${formId} #${fields[i][0]}_colorpick`).colorpicker('setValue', object[fields[i][0]]);
          $(`${formId} #${fields[i][0]}`).val(object[fields[i][0]]);
        }
      } else if (fields[i][1].type === 'datetime' || fields[i][1].type === 'time') {
        if (object[fields[i][0]] !== '0000-00-00 00:00:00' && object[fields[i][0]] !== '' && object[fields[i][0]] != null && object[fields[i][0]] !== undefined) {
          const tempDate = object[fields[i][0]];
          const arr = tempDate.split(' ');
          const dateArr = arr[0].split('-');
          const timeArr = arr[1].split(':');
          $(`${formId} #${fields[i][0]}_datetime`).data('datetimepicker').setLocalDate(new Date(dateArr[0], parseInt(dateArr[1], 10) - 1, dateArr[2], timeArr[0], timeArr[1], timeArr[2]));
        }
      } else if (fields[i][1].type === 'label') {
        $(`${formId} #${fields[i][0]}`).html(object[fields[i][0]]);
      } else if (fields[i][1].type === 'placeholder') {
        if (fields[i][1]['remote-source'] !== undefined && fields[i][1]['remote-source'] != null) {
          // const key = `${fields[i][1]['remote-source'][0]}_${fields[i][1]['remote-source'][1]}_${fields[i][1]['remote-source'][2]}`;
          const key = this.getRemoteSourceKey(fields[i]);
          placeHolderVal = this.fieldMasterData[key][object[fields[i][0]]];
        } else {
          placeHolderVal = object[fields[i][0]];
        }

        if (placeHolderVal === undefined || placeHolderVal == null) {
          placeHolderVal = '';
        } else {
          try {
            placeHolderVal = placeHolderVal.replace(/(?:\r\n|\r|\n)/g, '<br />');
          } catch (e) {
            // Do nothing
          }
        }

        if (fields[i][1].formatter !== undefined && fields[i][1].formatter && $.isFunction(fields[i][1].formatter)) {
          try {
            placeHolderVal = fields[i][1].formatter(placeHolderVal);
          } catch (e) {
            // Do nothing
          }
        }

        $(`${formId} #${fields[i][0]}`).html(placeHolderVal);
      } else if (fields[i][1].type === 'fileupload') {
        if (object[fields[i][0]] != null && object[fields[i][0]] !== undefined && object[fields[i][0]] !== '') {
          $(`${formId} #${fields[i][0]}`).html(object[fields[i][0]]);
          $(`${formId} #${fields[i][0]}`).attr('val', object[fields[i][0]]);
          $(`${formId} #${fields[i][0]}`).show();
          $(`${formId} #${fields[i][0]}_download`).show();
          $(`${formId} #${fields[i][0]}_remove`).show();
        }
        if (fields[i][1].readonly === true) {
          $(`${formId} #${fields[i][0]}_upload`).remove();
        }
      } else if (fields[i][1].type === 'select') {
        if (object[fields[i][0]] === undefined || object[fields[i][0]] == null || object[fields[i][0]] === '') {
          object[fields[i][0]] = 'NULL';
        }
        $(`${formId} #${fields[i][0]}`).val(object[fields[i][0]]);
      } else if (fields[i][1].type === 'select2') {
        if (object[fields[i][0]] === undefined || object[fields[i][0]] == null || object[fields[i][0]] === '') {
          object[fields[i][0]] = 'NULL';
        }
        $(`${formId} #${fields[i][0]}`).select2('val', object[fields[i][0]]);
      } else if (fields[i][1].type === 'select2multi') {
        // TODO - SM
        if (object[fields[i][0]] === undefined || object[fields[i][0]] == null || object[fields[i][0]] === '') {
          object[fields[i][0]] = 'NULL';
        }

        let msVal = [];
        if (object[fields[i][0]] !== undefined && object[fields[i][0]] != null && object[fields[i][0]] !== '') {
          try {
            msVal = JSON.parse(object[fields[i][0]]);
          } catch (e) {
            // Do nothing
          }
        }

        $(`${formId} #${fields[i][0]}`).select2('val', msVal);
        const select2Height = $(`${formId} #${fields[i][0]}`).find('.select2-choices').height();
        $(`${formId} #${fields[i][0]}`).find('.controls').css('min-height', `${select2Height}px`);
        $(`${formId} #${fields[i][0]}`).css('min-height', `${select2Height}px`);
      } else if (fields[i][1].type === 'datagroup') {
        try {
          const html = this.dataGroupToHtml(object[fields[i][0]], fields[i]);
          $(`${formId} #${fields[i][0]}`).val(object[fields[i][0]]);
          $(`${formId} #${fields[i][0]}_div`).html('');
          $(`${formId} #${fields[i][0]}_div`).append(html);

          this.makeDataGroupSortable(fields[i], $(`${formId} #${fields[i][0]}_div_inner`));
        } catch (e) {
          // Do nothing
        }
      } else if (fields[i][1].type === 'signature') {
        if (object[fields[i][0]] !== '' || object[fields[i][0]] !== undefined
                    || object[fields[i][0]] != null) {
          $(`${formId} #${fields[i][0]}`).data('signaturePad').fromDataURL(object[fields[i][0]]);
        }
      } else if (fields[i][1].type === 'simplemde') {
        $(`${formId} #${fields[i][0]}`).data('simplemde').value(object[fields[i][0]]);
      } else if (fields[i][1].type === 'code') {
        const cm = $(`${formId} #${fields[i][0]}`).data('codemirror');
        if (cm) {
          cm.getDoc().setValue(object[fields[i][0]]);
        }
      } else {
        $(`${formId} #${fields[i][0]}`).val(object[fields[i][0]]);
      }
    }
  }

  /**
     * Cancel edit or add new on modules
     * @method cancel
     */

  cancel() {
    $(`#${this.getTableName()}Form`).hide();
    $(`#${this.getTableName()}`).show();
  }

  renderFormField(field) {
    let userId = 0;
    if (this.fieldTemplates[field[1].type] === undefined || this.fieldTemplates[field[1].type] == null) {
      return '';
    }
    let t = this.fieldTemplates[field[1].type];
    field[1].label = this.gt(field[1].label);
    if (field[1].validation !== 'none' && field[1].validation !== 'emailOrEmpty' && field[1].validation !== 'numberOrEmpty' && field[1].type !== 'placeholder' && field[1].label.indexOf('*') < 0) {
      const tempSelectBoxes = ['select', 'select2'];
      if (!(tempSelectBoxes.indexOf(field[1].type) >= 0 && field[1]['allow-null'] === true)) {
        field[1].label = `${field[1].label}<font class="redFont">*</font>`;
      }
    }

    if (field[1].type === 'select' || field[1].type === 'select2' || field[1].type === 'select2multi') {
      t = t.replace(/_id_/g, field[0]);
      t = t.replace(/_label_/g, field[1].label);
      if (field[1].source !== undefined && field[1].source != null) {
        t = t.replace('_options_', this.renderFormSelectOptions(field[1].source, field));
      } else if (field[1]['remote-source'] !== undefined && field[1]['remote-source'] != null) {
        // let key = `${field[1]['remote-source'][0]}_${field[1]['remote-source'][1]}_${field[1]['remote-source'][2]}`;
        // if (field[1]['remote-source'].length === 4) {
        //   key = `${key}_${field[1]['remote-source'][3]}`;
        // }
        const key = this.getRemoteSourceKey(field);
        t = t.replace('_options_', this.renderFormSelectOptionsRemote(this.fieldMasterData[key], field));
      }
    } else if (field[1].type === 'colorpick') {
      t = t.replace(/_id_/g, field[0]);
      t = t.replace(/_label_/g, field[1].label);
    } else if (field[1].type === 'date') {
      t = t.replace(/_id_/g, field[0]);
      t = t.replace(/_label_/g, field[1].label);
    } else if (field[1].type === 'datetime') {
      t = t.replace(/_id_/g, field[0]);
      t = t.replace(/_label_/g, field[1].label);
    } else if (field[1].type === 'time') {
      t = t.replace(/_id_/g, field[0]);
      t = t.replace(/_label_/g, field[1].label);
    } else if (field[1].type === 'fileupload') {
      t = t.replace(/_id_/g, field[0]);
      t = t.replace(/_label_/g, field[1].label);
      const ce = this.getCurrentProfile();
      if (ce != null && ce !== undefined) {
        userId = ce.id;
      } else {
        userId = this.getUser().id * -1;
      }
      t = t.replace(/_userId_/g, userId);
      t = t.replace(/_group_/g, this.tab);

      if (field[1].filetypes !== undefined && field[1].filetypes != null) {
        t = t.replace(/_filetypes_/g, field[1].filetypes);
      } else {
        t = t.replace(/_filetypes_/g, 'all');
      }

      t = t.replace(/_rand_/g, this.generateRandom(14));
    } else if (field[1].type === 'datagroup') {
      t = t.replace(/_id_/g, field[0]);
      t = t.replace(/_label_/g, field[1].label);
    } else if (field[1].type === 'signature') {
      t = t.replace(/_id_/g, field[0]);
      t = t.replace(/_label_/g, field[1].label);
    } else if (field[1].type === 'tinymce' || field[1].type === 'simplemde') {
      t = t.replace(/_id_/g, field[0]);
      t = t.replace(/_label_/g, field[1].label);
    } else {
      t = t.replace(/_id_/g, field[0]);
      t = t.replace(/_label_/g, field[1].label);
    }


    if (field[1].validation !== undefined && field[1].validation != null && field[1].validation !== '') {
      t = t.replace(/_validation_/g, `validation="${field[1].validation}"`);
    } else {
      t = t.replace(/_validation_/g, '');
    }

    if (field[1].help !== undefined && field[1].help !== null) {
      t = t.replace(/_helpline_/g, field[1].help);
      t = t.replace(/_hidden_class_help_/g, '');
    } else {
      t = t.replace(/_helpline_/g, '');
      t = t.replace(/_hidden_class_help_/g, 'hide');
    }

    if (field[1].placeholder !== undefined && field[1].placeholder !== null) {
      t = t.replace(/_placeholder_/g, `placeholder="${field[1].placeholder}"`);
    } else {
      t = t.replace(/_placeholder_/g, '');
    }

    if (field[1].mask !== undefined && field[1].mask !== null) {
      t = t.replace(/_mask_/g, `mask="${field[1].mask}"`);
    } else {
      t = t.replace(/_mask_/g, '');
    }

    return t;
  }

  renderFormSelectOptions(options, field) {
    let html = '';

    if (field != null && field !== undefined) {
      if (field[1]['allow-null'] === true) {
        if (field[1]['null-label'] !== undefined && field[1]['null-label'] != null) {
          html += `<option value="NULL">${this.gt(field[1]['null-label'])}</option>`;
        } else {
          html += '<option value="NULL">Select</option>';
        }
      }
    }


    // Sort options

    const tuples = [];

    for (const key in options) {
      tuples.push(options[key]);
    }
    if (field[1].sort === true) {
      tuples.sort((a, b) => {
        a = a[1];
        b = b[1];

        // eslint-disable-next-line no-nested-ternary
        return a < b ? -1 : (a > b ? 1 : 0);
      });
    }


    for (let i = 0; i < tuples.length; i++) {
      const prop = tuples[i][0];
      const value = tuples[i][1];
      let t = '<option value="_id_">_val_</option>';
      t = t.replace('_id_', prop);
      t = t.replace('_val_', this.gt(value));
      html += t;
    }
    return html;
  }

  renderFormSelectOptionsRemote(options, field) {
    let html = '';
    if (field[1]['allow-null'] === true) {
      if (field[1]['null-label'] !== undefined && field[1]['null-label'] != null) {
        html += `<option value="NULL">${this.gt(field[1]['null-label'])}</option>`;
      } else {
        html += '<option value="NULL">Select</option>';
      }
    }

    // Sort options

    const tuples = [];

    for (const key in options) {
      tuples.push([key, options[key]]);
    }
    if (field[1].sort === 'true') {
      tuples.sort((a, b) => {
        a = a[1];
        b = b[1];

        // eslint-disable-next-line no-nested-ternary
        return a < b ? -1 : (a > b ? 1 : 0);
      });
    }

    for (let i = 0; i < tuples.length; i++) {
      const prop = tuples[i][0];
      const value = tuples[i][1];

      let t = '<option value="_id_">_val_</option>';
      t = t.replace('_id_', prop);
      t = t.replace('_val_', this.gt(value));
      html += t;
    }


    return html;
  }

  setCustomTemplates(templates) {
    this.customTemplates = templates;
  }

  setEmailTemplates(templates) {
    this.emailTemplates = templates;
  }

  getCustomTemplate(file) {
    return this.customTemplates[file];
  }

  setFieldTemplates(templates) {
    this.fieldTemplates = templates;
  }


  getMetaFieldForRendering(fieldName) {
    return '';
  }

  clearDeleteParams() {
    this.deleteParams = {};
  }

  getShowAddNew() {
    return this.showAddNew;
  }

  /**
     * Override this method to change add new button label
     * @method getAddNewLabel
     */

  getAddNewLabel() {
    return 'Add New';
  }

  /**
     * Used to set whether to show the add new button for a module
     * @method setShowAddNew
     * @param showAddNew {Boolean} value
     */

  setShowAddNew(showAddNew) {
    this.showAddNew = showAddNew;
  }

  /**
     * Used to set whether to show delete button for each entry in module
     * @method setShowDelete
     * @param val {Boolean} value
     */
  setShowDelete(val) {
    this.showDelete = val;
  }


  /**
     * Used to set whether to show edit button for each entry in module
     * @method setShowEdit
     * @param val {Boolean} value
     */

  setShowEdit(val) {
    this.showEdit = val;
  }

  /**
     * Used to set whether to show save button in form
     * @method setShowSave
     * @param val {Boolean} value
     */


  setShowSave(val) {
    this.showSave = val;
  }


  /**
     * Used to set whether to show cancel button in form
     * @method setShowCancel
     * @param val {Boolean} value
     */

  setShowCancel(val) {
    this.showCancel = val;
  }

  /**
     * Datatable option array will be extended with associative array provided here
     * @method getCustomTableParams
     * @param val {Boolean} value
     */


  getCustomTableParams() {
    return {};
  }

  getActionButtons(obj) {
    return modJs.getActionButtonsHtml(obj.aData[0], obj.aData);
  }


  /**
     * This return html for action buttons in each row. Override this method if you need to make changes to action buttons.
     * @method getActionButtonsHtml
     * @param id {int} id of the row
     * @param data {Array} data for the row
     * @returns {String} html for action buttons
     */

  getActionButtonsHtml(id, data) {
    const editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
    const deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
    const cloneButton = '<img class="tableActionButton" src="_BASE_images/clone.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Copy" onclick="modJs.copyRow(_id_);return false;"></img>';
    let html = '<div style="width:80px;">_edit__delete__clone_</div>';

    if (this.showAddNew) {
      html = html.replace('_clone_', cloneButton);
    } else {
      html = html.replace('_clone_', '');
    }

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
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }


  /**
     * Generates a random string
     * @method generateRandom
     * @param length {int} required length of the string
     * @returns {String} random string
     */

  generateRandom(length) {
    const d = new Date();
    const chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    let result = '';
    for (let i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result + d.getTime();
  }


  checkFileType(elementName, fileTypes) {
    const fileElement = document.getElementById(elementName);
    let fileExtension = '';
    if (fileElement.value.lastIndexOf('.') > 0) {
      fileExtension = fileElement.value.substring(fileElement.value.lastIndexOf('.') + 1, fileElement.value.length);
    }

    fileExtension = fileExtension.toLowerCase();

    const allowed = fileTypes.split(',');

    if (allowed.indexOf(fileExtension) < 0) {
      fileElement.value = '';
      this.showMessage('File Type Error', 'Selected file type is not supported');
      this.clearFileElement(elementName);
      return false;
    }

    return true;
  }

  clearFileElement(elementName) {
    let control = $(`#${elementName}`);
    control.replaceWith(control = control.val('').clone(true));
  }


  fixJSON(json) {
    if (this.noJSONRequests === '1') {
      json = window.btoa(json);
    }
    return json;
  }


  getClientDate(date) {
    const offset = this.getClientGMTOffset();
    const tzDate = date.addMinutes(offset * 60);
    return tzDate;
  }

  getClientGMTOffset() {
    const rightNow = new Date();
    const jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);
    const temp = jan1.toGMTString();
    const jan2 = new Date(temp.substring(0, temp.lastIndexOf(' ') - 1));
    return (jan1 - jan2) / (1000 * 60 * 60);
  }

  /**
     * Override this method in a module to provide the help link for the module. Help link of the module on frontend will get updated with this.
     * @method getHelpLink
     * @returns {String} help link
     */

  getHelpLink() {
    return null;
  }

  showLoader() {
    $('#iceloader').show();
  }

  hideLoader() {
    $('#iceloader').hide();
  }

  generateOptions(data) {
    const template = '<option value="__val__">__text__</option>';
    let options = '';
    for (const index in data) {
      options += template.replace('__val__', index).replace('__text__', data[index]);
    }

    return options;
  }

  isModuleInstalled(type, name) {
    if (modulesInstalled === undefined || modulesInstalled === null) {
      return false;
    }

    return (modulesInstalled[`${type}_${name}`] === 1);
  }


  setCustomFields(fields) {
    let field; let
      parsed;
    for (let i = 0; i < fields.length; i++) {
      field = fields[i];
      if (field.display !== 'Hidden' && field.data !== '' && field.data !== undefined) {
        try {
          parsed = JSON.parse(field.data);
          if (parsed === undefined || parsed == null) {
            continue;
          } else if (parsed.length !== 2) {
            continue;
          } else if (parsed[1].type === undefined || parsed[1].type == null) {
            continue;
          }
          this.customFields.push(parsed);
        } catch (e) {
          // Do nothing
        }
      }
    }
  }

  addCustomFields(fields) {
    for (let i = 0; i < this.customFields.length; i++) {
      fields.push(this.customFields[i]);
    }

    return fields;
  }

  getImageUrlFromName(firstName, lastName) {
    let seed = firstName.substring(0, 1);
    if (!lastName && lastName.length > 0) {
      seed += firstName.substring(firstName.length - 1, 1);
    } else {
      seed += lastName.substring(0, 1);
    }

    const arr = `${firstName}${lastName}`.split('');
    seed += arr.reduce((acc, item) => parseInt(item.charCodeAt(0), 10) + acc, 0);

    return `https://avatars.dicebear.com/api/initials/:${seed}.svg`;
  }

  downloadPdf(type, data) {
    const url = `${this.clientUrl}service.php?a=pdf&h=${type}&data=${data}`;
    window.open(url, '_blank');
  }

  checkIfUserEmailIsGoogleDomain(domain) {
    const url = `https://dns.google.com/resolve?name=${domain}&type=MX`;
    $.get(url, (data) => {
      if (data == null || data.Answer == null) {
        return;
      }
      const hasGoogle = data.Answer.filter((item) => item.data != null && item.data.includes('google.com'));
      if (hasGoogle.length > 0) {
        $('#googleConnectModel').modal({
          backdrop: 'static',
        });
      }
    });
  }
}

export default ModuleBase;
