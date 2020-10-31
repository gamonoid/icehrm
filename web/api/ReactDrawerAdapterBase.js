import React from 'react';
import ReactDOM from 'react-dom';
import AdapterBase from './AdapterBase';
import IceFormDrawer from '../components/IceFormDrawer';

class ReactDrawerAdapterBase extends AdapterBase {
  initForm() {
    this.formContainer = React.createRef();
    ReactDOM.render(
      <IceFormDrawer
        ref={this.formContainer}
        fields={this.getFormFields()}
        adapter={this}
        formReference={this.formReference}
      />,
      document.getElementById(`${this.tab}FormReact`),
    );
  }

  renderForm(object) {
    this.initForm();
    this.formContainer.current.show(object);
  }
}

export default ReactDrawerAdapterBase;
