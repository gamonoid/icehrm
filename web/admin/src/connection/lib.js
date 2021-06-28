/**
 * Author: Thilina Hasantha
 */
import React from 'react';
import ReactDOM from 'react-dom';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import ConnectionTab from './components/ConnectionTab';

/**
 * AssetTypeAdapter
 */

class ConnectionAdapter extends ReactModalAdapterBase {
  constructor(data) {
    super('', '', '', '');
    this.data = data;
  }

  get(callBackData) {
    const { components } = this.data;
    ReactDOM.render(
      <ConnectionTab {...components}/>,
      document.getElementById('connectionData'),
    );
  }

  initSourceMappings() {

  }
}

module.exports = { ConnectionAdapter };
