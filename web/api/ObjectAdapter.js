/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from './AdapterBase';
/**
 * ObjectAdapter
 */

class ObjectAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.container = null;
    this.loadMoreButton = null;
    this.start = 0;
    this.pageSize = 6;
    this.currentPage = 1;
    this.hasMoreData = true;
    this.searchTerm = '';
    this.searchInput = null;
  }

  getObjectHTML(object) {
    const template = this.getCustomTemplate(this.getTemplateName());
    let t = template;
    for (const index in object) {
      t = t.replace(new RegExp(`#_${index}_#`, 'g'), object[index]);
    }
    return t;
  }

  setPageSize(pageSize) {
    this.pageSize = pageSize;
  }

  addDomEvents(object) {

  }

  getTemplateName() {
    return '';
  }

  renderObject(object) {
    const objDom = this.getObjectDom(object.id);

    const html = this.getObjectHTML(object);
    const domObj = $(html);


    if (objDom !== undefined && objDom != null) {
      objDom.replace(domObj);
    } else {
      this.container.append(domObj);
    }

    this.addDomEvents(domObj);
  }

  setContainer(container) {
    this.container = container;
  }

  setLoadMoreButton(loadMoreButton) {
    const that = this;
    this.loadMoreButton = loadMoreButton;
    this.loadMoreButton.off().on('click', () => {
      that.loadMoreButton.attr('disabled', 'disabled');
      that.loadMore([]);
    });
  }

  showLoadError(msg) {
    $(`#${this.getTableName()}_error`).html(msg);
    $(`#${this.getTableName()}_error`).show();
  }

  hideLoadError() {
    $(`#${this.getTableName()}_error`).hide();
  }

  setSearchBox(searchInput) {
    const that = this;
    this.searchInput = searchInput;
    this.searchInput.off();
    this.searchInput.keydown(function (event) {
      const val = $(this).val();
      if (event.which === 13) {
        event.preventDefault();
        that.search([]);
      } else if ((event.which === 8 || event.which === 46) && val.length === 1 && that.searchTerm !== '') {
        that.search([]);
      }
    });
  }

  getObjectDom(id) {
    const obj = this.container.find(`#obj_${id}`);
    if (obj.length) {
      return obj;
    }
    return null;
  }

  loadMore(callBackData) {
    if (!this.hasMoreData) {
      return;
    }
    this.currentPage++;
    this.get(callBackData, true);
  }

  get(callBackData, loadMore) {
    const that = this;

    this.hideLoadError();

    if (!loadMore) {
      this.currentPage = 1;
      if (this.container != null) {
        this.container.html('');
      }
      this.hasMoreData = true;
      this.tableData = [];
    }

    this.start = (this.currentPage - 1) * this.pageSize;


    this.container = $(`#${this.getTableName()}`).find('.objectList');

    that.showLoader();


    let url = `${this.getDataUrl(that.getDataMapping())
    }&iDisplayStart=${this.start}&iDisplayLength=${this.pageSize}&objects=1`;

    if (this.searchTerm !== '' && this.searchTerm !== undefined && this.searchTerm != null) {
      url += `&sSearch=${this.searchTerm}`;
    }

    $.post(url, (data) => {
      that.getSuccessCallBack(callBackData, data);
    }, 'json').always(() => { that.hideLoader(); });

    that.initFieldMasterData();

    this.trackEvent('get', this.tab, this.table);
  }

  search(callBackData) {
    const that = this;
    this.searchTerm = $(`#${this.getTableName()}_search`).val();

    this.get(callBackData);
  }


  getSuccessCallBack(callBackData, serverData) {
    const data = [];

    if (serverData.length === 0 && this.container.html() === '') {
      this.showLoadError('No Results Found !!!');
      return;
    }

    if (this.getFilters() == null) {
      $(`#${this.getTableName()}_filterBtn`).hide();
      $(`#${this.getTableName()}_resetFilters`).hide();
    } else {
      $(`#${this.getTableName()}_filterBtn`).show();
      $(`#${this.getTableName()}_resetFilters`).show();
      if (this.currentFilterString !== '' && this.currentFilterString != null) {
        $(`#${this.getTableName()}_resetFilters`).html(`${this.currentFilterString}<i class="fa fa-times"></i>`);
      } else {
        $(`#${this.getTableName()}_resetFilters`).html('Reset Filters');
        $(`#${this.getTableName()}_resetFilters`).hide();
      }
    }

    $(`#${this.getTableName()}`).find('.search-controls').show();
    if (serverData.length > this.pageSize) {
      this.hasMoreData = true;
      serverData.pop();
      this.loadMoreButton.removeAttr('disabled');
      this.loadMoreButton.show();
    } else {
      this.hasMoreData = false;
      this.loadMoreButton.hide();
    }

    this.scrollToElementBottom(this.container);
    for (let i = 0; i < serverData.length; i++) {
      data.push(this.preProcessTableData(serverData[i]));
    }
    this.sourceData = serverData;
    if (callBackData.callBack !== undefined && callBackData.callBack != null) {
      if (callBackData.callBackData === undefined || callBackData.callBackData == null) {
        callBackData.callBackData = [];
      }
      callBackData.callBackData.push(serverData);
      callBackData.callBackData.push(data);
      this.callFunction(callBackData.callBack, callBackData.callBackData);
    }

    this.tableData = data;

    if (!(callBackData.noRender !== undefined && callBackData.noRender != null && callBackData.noRender === true)) {
      for (let i = 0; i < data.length; i++) {
        this.renderObject(data[i]);
      }
    }
  }
}

export default ObjectAdapter;
