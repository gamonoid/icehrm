/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from './AdapterBase';

class BaseGraphAdapter extends AdapterBase {
  getDataMapping() {
    return [];
  }

  getHeaders() {
    return [];
  }

  getFormFields() {
    return [];
  }

  // eslint-disable-next-line no-unused-vars
  createTable(elementId) {

  }
}

export default BaseGraphAdapter;
