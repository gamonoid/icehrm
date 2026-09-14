/**
 * Each extension should define and expose a module class extending `ExtensionModuleBase`.
 * This is to make sure that the extension is compatible with the core features of IceHrm.
 */
import React from 'react';
import ReactDOM from "react-dom";
import ExtensionModuleBase from "../../../../../web/api/ExtensionModuleBase";
import MarketplaceAdminExtensionView from "./view";

class MarketplaceAdminModule extends ExtensionModuleBase {
  /**
   * This method should be used to mount the React component responsible for the extension view.
   * This method will be called after IceHrm core and frontend is loaded.
   */
  showExtensionView() {
    // Mounting your extension
    // The DOM element with id `content` is defined in the `index.php` file of the extension
    ReactDOM.render(
      <MarketplaceAdminExtensionView
        controller={window.marketplaceExtensionController}
        appWebUrl={window.marketplaceAppWebUrl}
      />,
      document.getElementById('content')
    );
  }
}

export default MarketplaceAdminModule;
