import React from 'react';
import ReactDOM from "react-dom";
import ExtensionModuleBase from "../../../../../web/api/ExtensionModuleBase";
import CompanyAssetsView from "./view";

class CompanyAssetsModule extends ExtensionModuleBase {
  showExtensionView() {
    ReactDOM.render(<CompanyAssetsView />, document.getElementById('content'));
  }
}

export default CompanyAssetsModule;
