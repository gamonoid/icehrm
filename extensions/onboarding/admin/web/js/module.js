import React from 'react';
import ReactDOM from 'react-dom';
import ExtensionModuleBase from '../../../../../web/api/ExtensionModuleBase';
import OnboardingAdminView from './view';

class OnboardingAdminModule extends ExtensionModuleBase {
  showExtensionView() {
    ReactDOM.render(<OnboardingAdminView apiClient={this.apiClient} />, document.getElementById('content'));
  }
}

export default OnboardingAdminModule;
