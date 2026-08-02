import React from 'react';
import ReactDOM from "react-dom";
import ExtensionModuleBase from "../../../../../web/api/ExtensionModuleBase";
import Advance_reportsAdminExtensionView from "./view";

class Advance_reportsAdminModule extends ExtensionModuleBase {
  showExtensionView() {
    ReactDOM.render(<Advance_reportsAdminExtensionView />, document.getElementById('content'));
  }
}

export default Advance_reportsAdminModule;
