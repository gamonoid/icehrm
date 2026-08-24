const axios = require('axios');

class IceDataPipe {
  constructor(adapter, pageSize = 10) {
    this.adapter = adapter;
    this.pageSize = pageSize;
  }

  readMetaData() {
    this.adapter.initFieldMasterData();
  }

  get({
    page, search, sortField, sortOrder, filters, limit,
  }) {
    const pageSize = limit || this.pageSize;
    const start = (page - 1) * pageSize;
    const dataUrl = this.getDataUrl(
      this.adapter.getDataMapping(),
      search,
      filters,
    );
    let url = `${dataUrl}&iDisplayStart=${start}&iDisplayLength=${pageSize}`;
    url = this.applySortingData(this.adapter.getDataMapping(), url, sortField, sortOrder);
    // $.post(url, (data) => {
    //   that.getSuccessCallBack(callBackData, data);
    // }, 'json').always(() => { that.hideLoader(); });
    url = `${url}&version=v2`;
    return axios.post(url, {})
      .then((data) => {
        this.adapter.checkSessionClose(data.data);
        const key = this.getRequestKey(page, search, sortField, sortOrder, filters, limit);
        const response = {
          items: data.data.objects,
          total: data.data.totalRecords,
        };
        if (this.adapter.localStorageEnabled) {
          window.localStorage.setItem(key, JSON.stringify(response));
        }

        return response;
      });
  }

  getCachedResponse({
    page, search, sortField, sortOrder, filters, limit,
  }) {
    const key = this.getRequestKey(page, search, sortField, sortOrder, filters, limit);
    const cachedResponse = window.localStorage.getItem(key);
    if (!cachedResponse) {
      return null;
    }

    return JSON.parse(cachedResponse);
  }

  clearCachedResponse({
    page, search, sortField, sortOrder, filters, limit,
  }) {
    const key = this.getRequestKey(page, search, sortField, sortOrder, filters, limit);
    window.localStorage.setItem(key, null);
  }

  getRequestKey(page, search, sortField, sortOrder, filters, limit) {
    return `${this.adapter.table}|${page}|${search}|${sortField}|${sortOrder}|${filters}|${limit}`;
  }

  applySortingData(columns, url, sortField, sortOrder) {
    let orderBy = '';
    if (sortField) {
      url = `${url}&sorting=1`;
      url = `${url}&iSortCol_0=${columns.indexOf(sortField)}`;
      url = `${url}&sSortDir_0=${(sortOrder === 'descend') ? 'DESC' : 'ASC'}`;
    } else if (this.adapter.getOrderBy() !== null) {
      // Setting the fix ordering
      orderBy = this.adapter.getOrderBy();
      url = `${url}&ob=${orderBy}`;
    }

    return url;
  }

  getDataUrl(_columns, searchTerm, filters) {
    const sourceMappingJson = JSON.stringify(this.adapter.getSourceMapping());

    const columns = JSON.stringify(_columns);

    let filterJson = '';
    if (this.adapter.getFilter() !== null) {
      filterJson = JSON.stringify(this.adapter.getFilter());
    }

    let url = this.adapter.moduleRelativeURL.replace('service.php', 'data.php');
    url = `${url}?t=${this.adapter.table}`;
    // URL-encode the JSON params. Without this, a '+' inside a mapping's display
    // field (e.g. Employee -> 'first_name+last_name') is decoded by PHP to a space,
    // so the server sees 'first_name last_name' as one column, the picker allowlist
    // (mappingFieldsAllowed) rejects it, and the FK is left as a raw id instead of
    // the resolved name. Encoding keeps the '+' intact so the name resolves.
    url = `${url}&sm=${encodeURIComponent(sourceMappingJson)}`;
    url = `${url}&cl=${encodeURIComponent(columns)}`;
    url = `${url}&ft=${encodeURIComponent(filterJson)}`;

    if (searchTerm && searchTerm.trim() !== '') {
      url += `&sSearch=${searchTerm}`;
    }

    if (this.adapter.isSubProfileTable()) {
      url = `${url}&type=sub`;
    }

    if (this.adapter.remoteTableSkipProfileRestriction()) {
      url = `${url}&skip=1`;
    }

    // SPA: declare which module this request belongs to so the server derives
    // data scope per-request instead of from the shared session modulePath
    // (see docs/DATA_SCOPE_ISSUE.md). Legacy adapters don't set these, so the
    // params are omitted and legacy behaviour is unchanged.
    if (this.adapter.spaModuleGroup && this.adapter.spaModuleName) {
      url = `${url}&mg=${encodeURIComponent(this.adapter.spaModuleGroup)}`;
      url = `${url}&mn=${encodeURIComponent(this.adapter.spaModuleName)}`;
    }

    return url;
  }
}

export default IceDataPipe;
