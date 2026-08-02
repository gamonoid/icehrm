/**
 * Each extension should define and expose a module class extending `ExtensionModuleBase`.
 * This is to make sure that the extension is compatible with the core features of IceHrm.
 */
import React from 'react';
import ReactDOM from "react-dom";
import ExtensionModuleBase from "../../../../../web/api/ExtensionModuleBase";
import DemoModeAdminExtensionView from "./view";

class DemoModeAdminModule extends ExtensionModuleBase {
  /**
   * This method should be used to mount the React component responsible for the extension view.
   * This method will be called after IceHrm core and frontend is loaded.
   */
  showExtensionView() {
    ReactDOM.render(<DemoModeAdminExtensionView />, document.getElementById('content'));
  }
}

export default DemoModeAdminModule;
