import React from 'react';
import ReactDOM from 'react-dom';
import ExtensionModuleBase from '../../../../../web/api/ExtensionModuleBase';
import DiscussionsAdminView from './view';

class DiscussionsAdminModule extends ExtensionModuleBase {
  showExtensionView() {
    ReactDOM.render(<DiscussionsAdminView />, document.getElementById('content'));
  }
}

export default DiscussionsAdminModule;
