import React from 'react';
import EditorUserExtensionController from './controller';
import EditorUserModule from './module';
import Checklist from "./checklist/dist/checklist.umd";
import Quiz from "./quiz/dist/bundle";
import ReactDOM from "react-dom";
import EditorUserExtensionView from "./view";
import EmployeeSelect from "./components/EmployeeSelect";

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new EditorUserModule('EditorUser');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.editorExtensionController = new EditorUserExtensionController(
    'user=editor',
    data.controller_url,
  );
}

function selectEmployee(element) {
  ReactDOM.unmountComponentAtNode(document.getElementById('EmployeeSelect'));
  ReactDOM.render(<EmployeeSelect element={element}/>, document.getElementById('EmployeeSelect'));
}

class IceEditorJsImage {
  static get toolbox() {
    return {
      title: 'Image',
      icon: '<svg width="17" height="15" viewBox="0 0 336 276" xmlns="http://www.w3.org/2000/svg"><path d="M291 150V79c0-19-15-34-34-34H79c-19 0-34 15-34 34v42l67-44 81 72 56-29 42 30zm0 52l-43-30-56 30-81-67-66 39v23c0 19 15 34 34 34h178c17 0 31-13 34-29zM79 0h178c44 0 79 35 79 79v118c0 44-35 79-79 79H79c-44 0-79-35-79-79V79C0 35 35 0 79 0z"/></svg>',
    };
  }

  render() {
    return document.createElement('input');
  }

  save(blockContent) {
    return {
      url: blockContent.value,
    };
  }
}


window.initEditorUser = init;
window.IceEditorJsImage = IceEditorJsImage;
window.EmployeeChecklist = Checklist;
window.Quiz = Quiz;
window.selectEmployee = selectEmployee;
