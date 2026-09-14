import {
  CountryAdapter,
  ProvinceAdapter,
  CurrencyTypeAdapter,
  NationalityAdapter,
  ImmigrationStatusAdapter,
  EthnicityAdapter,
} from './lib';

window.CountryAdapter = CountryAdapter;
window.ProvinceAdapter = ProvinceAdapter;
window.CurrencyTypeAdapter = CurrencyTypeAdapter;
window.NationalityAdapter = NationalityAdapter;
window.ImmigrationStatusAdapter = ImmigrationStatusAdapter;
window.EthnicityAdapter = EthnicityAdapter;

import IceDataPipe from '../../../api/IceDataPipe';

// Shared init — used by both the legacy page and the SPA shell (see
// NativeModuleRegistry admin/metadata). Six master-data lookup tabs.
function init(data) {
  const modJsList = {};
  const permissions = (data && data.permissions) || {};

  const wire = (key, Adapter, endPoint, tab, typeName, orderBy) => {
    const m = new Adapter(endPoint, tab, '', orderBy || '');
    m.setObjectTypeName(typeName);
    m.setDataPipe(new IceDataPipe(m));
    m.setAccess(permissions[endPoint] || []);
    modJsList[key] = m;
    return m;
  };

  wire('tabCountry', CountryAdapter, 'Country', 'Country', 'Country');
  wire('tabProvince', ProvinceAdapter, 'Province', 'Province', 'Province');
  wire('tabCurrencyType', CurrencyTypeAdapter, 'CurrencyType', 'CurrencyType', 'Currency Type');
  wire('tabNationality', NationalityAdapter, 'Nationality', 'Nationality', 'Nationality');
  wire('tabEthnicity', EthnicityAdapter, 'Ethnicity', 'Ethnicity', 'Ethnicity');
  wire('tabImmigrationStatus', ImmigrationStatusAdapter, 'ImmigrationStatus', 'ImmigrationStatus', 'Immigration Status');

  window.modJs = modJsList.tabCountry;
  window.modJsList = modJsList;
}

window.initAdminMetadata = init;
