/**
 * Each extension should define and expose a module class extending `ExtensionModuleBase`.
 * This is to make sure that the extension is compatible with the core features of IceHrm.
 */
import React from 'react';
import ReactDOM from "react-dom";
import ExtensionModuleBase from "../../../../../web/api/ExtensionModuleBase";
import Advance_reportsUserExtensionView from "./view";

class Advance_reportsUserModule extends ExtensionModuleBase {
  /**
   * This method should be used to mount the React component responsible for the extension view.
   * This method will be called after IceHrm core and frontend is loaded.
   */
  showExtensionView() {
    // Mounting your extension
    // The DOM element with id `content` is defined in the `index.php` file of the extension
    ReactDOM.render(<Advance_reportsUserExtensionView />, document.getElementById('content'));

    // Calling a method in the controller (just to show how it works)
    window.advance_reportsExtensionController.handleTestAction();

    // Sending some test api requests to endpoints defined in ApiController.php
    window.advance_reportsExtensionController.makeSomeTestApiRequests();
  }
}

export default Advance_reportsUserModule;
