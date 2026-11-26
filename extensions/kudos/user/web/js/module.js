import React from 'react';
import ReactDOM from 'react-dom';
import ExtensionModuleBase from '../../../../../web/api/ExtensionModuleBase';
import KudosUserView from './view';

class KudosUserModule extends ExtensionModuleBase {
  showExtensionView() {
    ReactDOM.render(<KudosUserView />, document.getElementById('content'));
  }
}

export default KudosUserModule;
