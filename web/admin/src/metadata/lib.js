/*
Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';
import IdNameAdapter from '../../../api/IdNameAdapter';
/**
 * CountryAdapter
 */

class CountryAdapter extends AdapterBase {
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

class ProvinceAdapter extends AdapterBase {
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

class CurrencyTypeAdapter extends AdapterBase {
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

class NationalityAdapter extends IdNameAdapter {

}

/**
 * ImmigrationStatusAdapter
 */

class ImmigrationStatusAdapter extends IdNameAdapter {

}


/**
 * EthnicityAdapter
 */

class EthnicityAdapter extends IdNameAdapter {

}

module.exports = {
  CountryAdapter,
  ProvinceAdapter,
  CurrencyTypeAdapter,
  NationalityAdapter,
  ImmigrationStatusAdapter,
  EthnicityAdapter,
};
