/* global baseUrl, clientUrl */
/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import IceApiClient from './IceApiClient';
import ModuleBase from './ModuleBase';
import RequestCache from '../api-common/RequestCache';
import MasterDataReader from './MasterDataReader';

class AdapterBase extends ModuleBase {
  constructor(endPoint, tab, filter, orderBy) {
    super();
    this.moduleRelativeURL = null;
    this.tableData = [];
    this.sourceData = [];
    this.filter = null;
    this.origFilter = null;
    this.orderBy = null;
    this.currentElement = null;
    this.initAdapter(endPoint, tab, filter, orderBy);
  }

  initAdapter(endPoint, tab, filter, orderBy) {
    this.moduleRelativeURL = baseUrl;
    this.table = endPoint;
    if (tab === undefined || tab === null) {
      this.tab = endPoint;
    } else {
      this.tab = tab;
    }

    if (filter === undefined || filter === null) {
      this.filter = null;
    } else {
      this.filter = filter;
    }

    this.origFilter = this.filter;

    if (orderBy === undefined || orderBy === null) {
      this.orderBy = null;
    } else {
      this.orderBy = orderBy;
    }

    this.trackEvent('initAdapter', tab);

    this.requestCache = new RequestCache();
  }

  initMasterDataReader() {
    this.masterDataReader = new MasterDataReader(this);
  }

  setupApiClient(token) {
    this.apiClient = new IceApiClient(this.apiUrl, token);
  }

  setApiUrl(apiUrl) {
    this.apiUrl = apiUrl;
  }

  setFilter(filter) {
    this.filter = filter;
  }

  preSetFilterExternal(filter) {
    this.initialFilter = filter;
  }

  setFilterExternal(_filter) {
    let filter = _filter;
    if (filter === undefined || filter === null) {
      filter = this.initialFilter;
    }

    if (filter === undefined || filter === null) {
      return;
    }

    this.setFilter(filter);
    this.filtersAlreadySet = true;
    $(`#${this.getTableName()}_resetFilters`).show();
    this.currentFilterString = this.getFilterString(filter);
  }

  getFilter() {
    return this.filter;
  }

  setOrderBy(orderBy) {
    this.orderBy = orderBy;
  }

  getOrderBy() {
    return this.orderBy;
  }

  getFile(name) {
    this.trackEvent('file', name);
    return new Promise((resolve, reject) => {
      $.getJSON(this.moduleRelativeURL, { a: 'file', name }, (data) => {
        if (data.status === 'SUCCESS') {
          resolve(data.data);
        } else {
          reject();
        }
      }).fail(() => reject());
    });
  }

  /**
     * @method add
     * @param object {Array} object data to be added to database
     * @param getFunctionCallBackData {Array} once a success is returned call get() function for this module with these parameters
     * @param _callGetFunction {Boolean} if false the get function of the module will not be called (default: true)
     * @param successCallback {Function} this will get called after success response
     */

  add(object, getFunctionCallBackData, callGetFunction, successCallback) {
    const that = this;
    if (callGetFunction === undefined || callGetFunction === null) {
      // eslint-disable-next-line no-param-reassign
      callGetFunction = true;
    }
    $(object).attr('a', 'add');
    $(object).attr('t', this.table);
    that.showLoader();
    this.requestCache.invalidateTable(this.table);
    $.post(this.moduleRelativeURL, object, (data) => {
      if (data.status === 'SUCCESS') {
        that.addSuccessCallBack(getFunctionCallBackData, data.object, callGetFunction, successCallback, that);
      } else {
        that.addFailCallBack(getFunctionCallBackData, data.object);
      }
    }, 'json')
      .fail((e) => {
        if (e.status === 403) {
          that.showMessage('Access Forbidden', e.responseJSON.message);
        }
      })
      .always(() => { that.hideLoader(); });
    this.trackEvent('add', this.tab, this.table);
  }

  addSuccessCallBack(callBackData, serverData, callGetFunction, successCallback, thisObject) {
    if (callGetFunction) {
      this.get(callBackData);
    }
    this.initFieldMasterData();
    if (successCallback !== undefined && successCallback !== null) {
      successCallback.apply(thisObject, [serverData]);
    }
    this.trackEvent('addSuccess', this.tab, this.table);
  }

  addFailCallBack(callBackData, serverData) {
    try {
      this.closePlainMessage();
    } catch (e) {
      // No need to report
    }
    this.showMessage('Error saving', serverData);
    this.trackEvent('addFailed', this.tab, this.table);
  }

  deleteObj(id, callBackData) {
    const that = this;
    that.showLoader();
    this.requestCache.invalidateTable(this.table);
    $.post(this.moduleRelativeURL, { t: this.table, a: 'delete', id }, (data) => {
      if (data.status === 'SUCCESS') {
        that.deleteSuccessCallBack(callBackData, data.object);
      } else {
        that.deleteFailCallBack(callBackData, data.object);
      }
    }, 'json')
      .fail((e) => {
        if (e.status === 403) {
          that.showMessage('Access Forbidden', e.responseJSON.message);
        }
      })
      .always(() => { that.hideLoader(); });
    this.trackEvent('delete', this.tab, this.table);
  }

  // eslint-disable-next-line no-unused-vars
  deleteSuccessCallBack(callBackData, serverData) {
    this.get(callBackData);
    this.clearDeleteParams();
  }

  deleteFailCallBack(callBackData, serverData) {
    this.clearDeleteParams();
    this.showMessage('Error Occurred while Deleting Item', serverData);
  }

  get(callBackData) {
    const that = this;

    if (this.getRemoteTable()) {
      this.createTableServer(this.getTableName());
      $(`#${this.getTableName()}Form`).hide();
      $(`#${this.getTableName()}`).show();
      return;
    }

    let sourceMappingJson = JSON.stringify(this.getSourceMapping());

    let filterJson = '';
    if (this.getFilter() !== null) {
      filterJson = JSON.stringify(this.getFilter());
    }

    let orderBy = '';
    if (this.getOrderBy() !== null) {
      orderBy = this.getOrderBy();
    }

    sourceMappingJson = this.fixJSON(sourceMappingJson);
    filterJson = this.fixJSON(filterJson);

    that.showLoader();
    $.post(this.moduleRelativeURL, {
      t: this.table, a: 'get', sm: sourceMappingJson, ft: filterJson, ob: orderBy,
    }, (data) => {
      if (data.status === 'SUCCESS') {
        that.getSuccessCallBack(callBackData, data.object);
      } else {
        that.getFailCallBack(callBackData, data.object);
      }
    }, 'json')
      .fail((e) => {
        if (e.status === 403) {
          that.showMessage('Access Forbidden', e.responseJSON.message);
        }
      })
      .always(() => { that.hideLoader(); });

    that.initFieldMasterData();

    this.trackEvent('get', this.tab, this.table);
    // var url = this.getDataUrl();
    // console.log(url);
  }


  getDataUrl(_columns) {
    const sourceMappingJson = JSON.stringify(this.getSourceMapping());

    const columns = JSON.stringify(_columns);

    let filterJson = '';
    if (this.getFilter() !== null) {
      filterJson = JSON.stringify(this.getFilter());
    }

    let orderBy = '';
    if (this.getOrderBy() !== null) {
      orderBy = this.getOrderBy();
    }

    let url = this.moduleRelativeURL.replace('service.php', 'data.php');
    url = `${url}?t=${this.table}`;
    url = `${url}&sm=${this.fixJSON(sourceMappingJson)}`;
    url = `${url}&cl=${this.fixJSON(columns)}`;
    url = `${url}&ft=${this.fixJSON(filterJson)}`;
    url = `${url}&ob=${orderBy}`;

    if (this.isSubProfileTable()) {
      url = `${url}&type=sub`;
    }

    if (this.remoteTableSkipProfileRestriction()) {
      url = `${url}&skip=1`;
    }

    return url;
  }

  isSubProfileTable() {
    return false;
  }

  remoteTableSkipProfileRestriction() {
    return false;
  }

  preProcessTableData(row) {
    return row;
  }

  getSuccessCallBack(callBackData, serverData) {
    const data = [];
    const mapping = this.getDataMapping();
    for (let i = 0; i < serverData.length; i++) {
      const row = [];
      for (let j = 0; j < mapping.length; j++) {
        row[j] = serverData[i][mapping[j]];
      }
      data.push(this.preProcessTableData(row));
    }
    this.sourceData = serverData;
    if (callBackData.callBack !== undefined && callBackData.callBack !== null) {
      if (callBackData.callBackData === undefined || callBackData.callBackData === null) {
        callBackData.callBackData = [];
      }
      callBackData.callBackData.push(serverData);
      callBackData.callBackData.push(data);
      this.callFunction(callBackData.callBack, callBackData.callBackData);
    }

    this.tableData = data;

    if (!(callBackData.noRender !== undefined && callBackData.noRender !== null && callBackData.noRender === true)) {
      this.createTable(this.getTableName());
      $(`#${this.getTableName()}Form`).hide();
      $(`#${this.getTableName()}`).show();
    }
  }

  // eslint-disable-next-line no-unused-vars
  getFailCallBack(callBackData, serverData) {

  }


  getElement(id, callBackData, clone) {
    const that = this;
    let sourceMappingJson = JSON.stringify(this.getSourceMapping());
    sourceMappingJson = this.fixJSON(sourceMappingJson);
    that.showLoader();
    $.post(this.moduleRelativeURL, {
      t: this.table, a: 'getElement', id, sm: sourceMappingJson,
    }, function (data) {
      if (data.status === 'SUCCESS') {
        if (clone) {
          delete data.object.id;
        }
        this.currentElement = data.object;
        that.getElementSuccessCallBack.apply(that, [callBackData, data.object]);
      } else {
        that.getElementFailCallBack.apply(that, [callBackData, data.object]);
      }
    }, 'json')
      .fail((e) => {
        if (e.status === 403) {
          that.showMessage('Access Forbidden', e.responseJSON.message);
        }
      })
      .always(() => { that.hideLoader(); });
    this.trackEvent('getElement', this.tab, this.table);
  }

  getElementSuccessCallBack(callBackData, serverData) {
    if (callBackData.callBack !== undefined && callBackData.callBack !== null) {
      if (callBackData.callBackData === undefined || callBackData.callBackData === null) {
        callBackData.callBackData = [];
      }
      callBackData.callBackData.push(serverData);
      this.callFunction(callBackData.callBack, callBackData.callBackData, this);
    }
    this.currentElement = serverData;
    if (!(callBackData.noRender !== undefined && callBackData.noRender !== null && callBackData.noRender === true)) {
      this.renderForm(serverData);
    }
  }

  // eslint-disable-next-line no-unused-vars
  getElementFailCallBack(callBackData, serverData) {

  }


  getTableData() {
    return this.tableData;
  }

  getTableName() {
    return this.tab;
  }

  getFieldValues(fieldMaster, callBackData) {
    const that = this;
    let method = '';
    let methodParams = '';
    if (fieldMaster[3] !== undefined && fieldMaster[3] !== null) {
      // eslint-disable-next-line prefer-destructuring
      method = fieldMaster[3];
    }

    if (fieldMaster[4] !== undefined && fieldMaster[4] !== null) {
      methodParams = JSON.stringify(fieldMaster[4]);
    }

    const key = this.requestCache.getKey(this.moduleRelativeURL, {
      t: fieldMaster[0], key: fieldMaster[1], value: fieldMaster[2], method, methodParams, a: 'getFieldValues',
    });
    const cacheData = this.requestCache.getData(key);

    if (cacheData !== null && cacheData !== undefined) {
      if (cacheData.status === 'SUCCESS') {
        callBackData.callBackData.push(cacheData.data);
        if (callBackData.callBackSuccess !== null && callBackData.callBackSuccess !== undefined) {
          callBackData.callBackData.push(callBackData.callBackSuccess);
        }
        that.callFunction(callBackData.callBack, callBackData.callBackData);
      }
    } else {
      const callbackWraper = function (data) {
        if (data.status === 'SUCCESS') {
          that.requestCache.setData(this.success.key, data);
          const localCallBackData = callBackData;
          localCallBackData.callBackData = [callBackData.callBackData[0]];
          localCallBackData.callBackData.push(data.data);
          if (localCallBackData.callBackSuccess !== null && localCallBackData.callBackSuccess !== undefined) {
            localCallBackData.callBackData.push(callBackData.callBackSuccess);
          }
          that.callFunction(localCallBackData.callBack, localCallBackData.callBackData);
        } else if (data.message === 'Access violation') {
          alert(`Error : ${callbackWraper.table} ${data.message}`);
        }
      };

      callbackWraper.key = key;
      // eslint-disable-next-line prefer-destructuring
      callbackWraper.table = fieldMaster[0];

      $.post(this.moduleRelativeURL, {
        t: fieldMaster[0], key: fieldMaster[1], value: fieldMaster[2], method, methodParams, a: 'getFieldValues',
      }, callbackWraper, 'json');
    }
  }

  setAdminProfile(empId) {
    try {
      localStorage.clear();
    } catch (e) {
      // No need to report
    }
    $.post(this.moduleRelativeURL, { a: 'setAdminEmp', empid: empId }, () => {
      // eslint-disable-next-line no-restricted-globals
      top.location.href = clientUrl;
    }, 'json');
  }

  customAction(subAction, module, request, callBackData, isPost) {
    const that = this;
    request = this.fixJSON(request);
    if (!isPost) {
      $.getJSON(this.moduleRelativeURL, {
        t: this.table, a: 'ca', sa: subAction, mod: module, req: request,
      }, (data) => {
        if (data.status === 'SUCCESS') {
          callBackData.callBackData.push(data.data);
          that.callFunction(callBackData.callBackSuccess, callBackData.callBackData);
        } else {
          callBackData.callBackData.push(data.data);
          that.callFunction(callBackData.callBackFail, callBackData.callBackData);
        }
      });
    } else {
      $.post(this.moduleRelativeURL, {
        t: this.table, a: 'ca', sa: subAction, mod: module, req: request,
      }, (data) => {
        if (data.status === 'SUCCESS') {
          callBackData.callBackData.push(data.data);
          that.callFunction(callBackData.callBackSuccess, callBackData.callBackData);
        } else {
          callBackData.callBackData.push(data.data);
          that.callFunction(callBackData.callBackFail, callBackData.callBackData);
        }
      }, 'json');
    }
  }


  sendCustomRequest(action, params, successCallback, failCallback) {
    params.a = action;
    $.post(this.moduleRelativeURL, params, (data) => {
      if (data.status === 'SUCCESS') {
        successCallback(data.data);
      } else {
        failCallback(data.data);
      }
    }, 'json');
  }


  getCustomActionUrl(action, params) {
    params.a = action;
    let str = '';
    for (const key in params) {
      if (params.hasOwnProperty(key)) {
        if (str !== '') {
          str += '&';
        }
        str += `${key}=${params[key]}`;
      }
    }
    return `${this.moduleRelativeURL}?${str}`;
  }


  getClientDataUrl() {
    return `${this.moduleRelativeURL.replace('service.php', '')}data/`;
  }

  getCustomUrl(str) {
    return this.moduleRelativeURL.replace('service.php', str);
  }
}

export default AdapterBase;
