import React from 'react';
import ReactDOM from 'react-dom';
import ExtensionModuleBase from '../../../../../web/api/ExtensionModuleBase';
import InboxUserView from './view';

class InboxUserModule extends ExtensionModuleBase {
  showExtensionView() {
    ReactDOM.render(<InboxUserView />, document.getElementById('content'));
  }
}

export default InboxUserModule;
