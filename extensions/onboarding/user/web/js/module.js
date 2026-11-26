import React from 'react';
import ReactDOM from 'react-dom';
import ExtensionModuleBase from '../../../../../web/api/ExtensionModuleBase';
import OnboardingUserView from './view';

class OnboardingUserModule extends ExtensionModuleBase {
  showExtensionView() {
    ReactDOM.render(<OnboardingUserView apiClient={this.apiClient} />, document.getElementById('content'));
  }
}

export default OnboardingUserModule;
