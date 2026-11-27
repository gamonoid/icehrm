import React from 'react';
import ReactDOM from 'react-dom';
import ExtensionModuleBase from '../../../../../web/api/ExtensionModuleBase';
import DiscussionsUserView from './view';

class DiscussionsUserModule extends ExtensionModuleBase {
  showExtensionView() {
    ReactDOM.render(<DiscussionsUserView />, document.getElementById('content'));
  }
}

export default DiscussionsUserModule;
