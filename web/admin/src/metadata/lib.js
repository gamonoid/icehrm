/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import ReactIdNameAdapter from '../../../api/ReactIdNameAdapter';
/**
 * CountryAdapter
 */

class CountryAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'code',
      'name',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Code' },
      { sTitle: 'Name' },
    ];
  }

  getTableColumns() {
    return [
      { title: 'Code', dataIndex: 'code', sorter: true },
      { title: 'Name', dataIndex: 'name', sorter: true },
    ];
  }

  showViewButton() {
    return false;
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['code', { label: 'Code', type: 'text', validation: '' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
    ];
  }
}


/**
 * ProvinceAdapter
 */

class ProvinceAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'code',
      'name',
      'country',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Code' },
      { sTitle: 'Name' },
      { sTitle: 'Country' },
    ];
  }

  getTableColumns() {
    return [
      { title: 'Code', dataIndex: 'code', sorter: true },
      { title: 'Name', dataIndex: 'name', sorter: true },
      { title: 'Country', dataIndex: 'country' },
    ];
  }

  showViewButton() {
    return false;
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['code', { label: 'Code', type: 'text', validation: '' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['country', { label: 'Country', type: 'select2', 'remote-source': ['Country', 'code', 'name'] }],
    ];
  }

  getFilters() {
    return [
      ['country', { label: 'Country', type: 'select2', 'remote-source': ['Country', 'code', 'name'] }],

    ];
  }
}

/**
 * CurrencyTypeAdapter
 */

class CurrencyTypeAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'code',
      'name',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Code' },
      { sTitle: 'Name' },
    ];
  }

  getTableColumns() {
    return [
      { title: 'Code', dataIndex: 'code', sorter: true },
      { title: 'Name', dataIndex: 'name', sorter: true },
    ];
  }

  showViewButton() {
    return false;
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['code', { label: 'Code', type: 'text', validation: '' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
    ];
  }
}


/**
 * NationalityAdapter
 */

class NationalityAdapter extends ReactIdNameAdapter {

}

/**
 * ImmigrationStatusAdapter
 */

class ImmigrationStatusAdapter extends ReactIdNameAdapter {

}


/**
 * EthnicityAdapter
 */

class EthnicityAdapter extends ReactIdNameAdapter {

}

module.exports = {
  CountryAdapter,
  ProvinceAdapter,
  CurrencyTypeAdapter,
  NationalityAdapter,
  ImmigrationStatusAdapter,
  EthnicityAdapter,
};
