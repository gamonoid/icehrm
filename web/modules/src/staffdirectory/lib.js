/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from '../../../api/AdapterBase';
import ObjectAdapter from '../../../api/ObjectAdapter';

class StaffDirectoryAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'image',
      'first_name',
      'last_name',
      'job_title',
      'department',
      'work_phone',
      'work_email',
      'joined_date',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: '' },
      { sTitle: 'First Name' },
      { sTitle: 'Last Name' },
      { sTitle: 'Job Title' },
      { sTitle: 'Department' },
      { sTitle: 'Work Phone' },
      { sTitle: 'Work Email' },
      { sTitle: 'Joined Date' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['first_name', { label: 'First Name', type: 'text', validation: '' }],
      ['last_name', { label: 'Last Name', type: 'text', validation: '' }],
      ['job_title', { label: 'Job Title', type: 'select2', 'remote-source': ['JobTitle', 'id', 'name'] }],
      ['department', { label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'] }],
      ['work_phone', { label: 'Work Phone', type: 'text', validation: 'none' }],
      ['work_email', { label: 'Work Email', type: 'placeholder', validation: 'emailOrEmpty' }],
      ['joined_date', { label: 'Joined Date', type: 'date', validation: '' }],
    ];
  }

  showActionButtons() {
    return false;
  }


  getCustomTableParams() {
    const that = this;
    const dataTableParams = {
      aoColumnDefs: [
        {
          fnRender(data, cell) {
            try {
              return that.preProcessRemoteTableData(data, cell, 1);
            } catch (e) { return cell; }
          },
          aTargets: [1],
        },
        {
          fnRender(data, cell) {
            try {
              return that.preProcessRemoteTableData(data, cell, 8);
            } catch (e) { return cell; }
          },
          aTargets: [8],
        },
      ],
    };
    return dataTableParams;
  }

  // eslint-disable-next-line consistent-return
  preProcessRemoteTableData(data, cell, id) {
    if (id === 8) {
      if (cell === '0000-00-00 00:00:00' || cell === '' || cell === undefined || cell === null) {
        return '';
      }
      return Date.parse(cell).toString('yyyy MMM d');
    } if (id === 1) {
      const tmp = '<img src="_img_" class="img-circle" style="width:45px;height: 45px;" alt="User Image">';
      return tmp.replace('_img_', cell);
    }
  }
}


/*
 StaffDirectoryObjectAdapter
 */


class StaffDirectoryObjectAdapter extends ObjectAdapter {
  getDataMapping() {
    return [
      'id',
      'image',
      'first_name',
      'last_name',
      'job_title',
      'department',
      'work_phone',
      'work_email',
      'joined_date',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: '' },
      { sTitle: 'First Name' },
      { sTitle: 'Last Name' },
      { sTitle: 'Job Title' },
      { sTitle: 'Department' },
      { sTitle: 'Work Phone' },
      { sTitle: 'Work Email' },
      { sTitle: 'Joined Date' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['first_name', { label: 'First Name', type: 'text', validation: '' }],
      ['last_name', { label: 'Last Name', type: 'text', validation: '' }],
      ['job_title', { label: 'Job Title', type: 'select2', 'remote-source': ['JobTitle', 'id', 'name'] }],
      ['department', { label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'] }],
      ['work_phone', { label: 'Work Phone', type: 'text', validation: 'none' }],
      ['work_email', { label: 'Work Email', type: 'placeholder', validation: 'emailOrEmpty' }],
      ['joined_date', { label: 'Joined Date', type: 'date', validation: '' }],
    ];
  }

  // eslint-disable-next-line no-unused-vars
  addDomEvents(object) {

  }

  getTemplateName() {
    return 'element.html';
  }

  preProcessTableData(_row) {
    const row = _row;
    row.color = this.getColorByRandomString(row.first_name);
    return row;
  }

  getFilters() {
    return [
      ['job_title', {
        label: 'Job Title', type: 'select2', 'allow-null': true, 'null-label': 'All Job Titles', 'remote-source': ['JobTitle', 'id', 'name'],
      }],
      ['department', {
        label: 'Department', type: 'select2', 'allow-null': true, 'null-label': 'All Departments', 'remote-source': ['CompanyStructure', 'id', 'title'],
      }],
    ];
  }
}


module.exports = {
  StaffDirectoryAdapter,
  StaffDirectoryObjectAdapter,
};
