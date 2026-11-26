import React from 'react';
import ReactDOM from 'react-dom';
import ExtensionModuleBase from '../../../../../web/api/ExtensionModuleBase';
import CompanyOnboardingAdminView from './view';

class CompanyOnboardingAdminModule extends ExtensionModuleBase {
  showExtensionView() {
    ReactDOM.render(<CompanyOnboardingAdminView apiClient={this.apiClient} />, document.getElementById('content'));
  }
}

export default CompanyOnboardingAdminModule;
