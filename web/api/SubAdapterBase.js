/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from './AdapterBase';
/**
 * @class SubAdapterBase
 * @param endPoint
 * @param tab
 * @param filter
 * @param orderBy
 * @returns
 */

class SubAdapterBase extends AdapterBase {
  deleteRow(id) {
    this.deleteParams.id = id;
    this.confirmDelete();
  }

  createTable(elementId) {
    let item; let itemHtml; let itemDelete; let itemEdit;
    const data = this.getTableData();

    const deleteButton = `<button id="#_id_#_delete" onclick="modJs.subModJsList['tab${elementId}'].deleteRow('_id_');return false;" type="button" style="position: absolute;bottom: 5px;right: 5px;font-size: 13px;" tooltip="Delete"><li class="fa fa-times"></li></button>`;
    const editButton = `<button id="#_id_#_edit" onclick="modJs.subModJsList['tab${elementId}'].edit('_id_');return false;" type="button" style="position: absolute;bottom: 5px;right: 35px;font-size: 13px;" tooltip="Edit"><li class="fa fa-edit"></li></button>`;

    const table = $('<div class="list-group"></div>');

    // add Header
    const header = this.getSubHeader();
    table.append(header);
    if (data.length === 0) {
      table.append(`<a href="#" class="list-group-item">${this.getNoDataMessage()}</a>`);
    } else {
      for (let i = 0; i < data.length; i++) {
        item = data[i];
        itemDelete = deleteButton.replace(/_id_/g, item[0]);
        itemEdit = editButton.replace(/_id_/g, item[0]);
        itemHtml = this.getSubItemHtml(item, itemDelete, itemEdit);
        table.append(itemHtml);
      }
    }
    $(`#${elementId}`).html('');
    $(`#${elementId}`).append(table);
    $('#plainMessageModel').modal('hide');
  }

  getNoDataMessage() {
    return 'No data found';
  }

  getSubHeader() {
    const header = $(`<a href="#" onclick="return false;" class="list-group-item" style="background:#eee;"><h4 class="list-group-item-heading">${this.getSubHeaderTitle()}</h4></a>`);
    return header;
  }
}


export default SubAdapterBase;
