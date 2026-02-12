/**
 * Each extension should define and expose a module class extending `ExtensionModuleBase`.
 * This is to make sure that the extension is compatible with the core features of IceHrm.
 */
import React from 'react';
import ReactDOM from "react-dom";
import ExtensionModuleBase from "../../../../../web/api/ExtensionModuleBase";
import EditorUserExtensionView from "./view";

class EditorUserModule extends ExtensionModuleBase {
  /**
   * This method should be used to mount the React component responsible for the extension view.
   * This method will be called after IceHrm core and frontend is loaded.
   */
  showExtensionView() {
    // Mounting your extension
    // The DOM element with id `content` is defined in the `index.php` file of the extension
    ReactDOM.render(<EditorUserExtensionView />, document.getElementById('content'));

    // Calling a method in the controller (just to show how it works)
    //window.editorExtensionController.handleTestAction();

    // Sending some test api requests to endpoints defined in ApiController.php
    //window.editorExtensionController.makeSomeTestApiRequests();
  }
}

export default EditorUserModule;
