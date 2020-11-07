const axios = require('axios');

class CustomAction {
  constructor(adapter) {
    this.adapter = adapter;
  }

  execute(subAction, module, request, isPost) {
    if (!isPost) {
      return axios.get(
        this.adapter.moduleRelativeURL,
        {
          params: {
            t: this.adapter.table, a: 'ca', sa: subAction, mod: module, req: request,
          },
        },
      );
    }

    return axios.post(this.moduleRelativeURL, {
      t: this.adapter.table, a: 'ca', sa: subAction, mod: module, req: request,
    });
  }
}

export default CustomAction;
