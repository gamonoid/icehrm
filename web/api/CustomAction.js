const axios = require('axios');

class CustomAction {
  constructor(adapter) {
    this.adapter = adapter;
  }

  execute(subAction, module, request, isPost) {
    // SPA: carry the per-request module scope so service.php resolves the right
    // MODULE_TYPE for the custom action (same as save/delete/element). Sent as
    // query params (read via $_REQUEST) so the POST body format is unchanged.
    const scope = (typeof this.adapter.spaScopeParams === 'function')
      ? this.adapter.spaScopeParams() : {};
    let url = this.adapter.moduleRelativeURL;
    if (scope.mg && scope.mn) {
      const sep = url.indexOf('?') === -1 ? '?' : '&';
      url = `${url}${sep}mg=${encodeURIComponent(scope.mg)}&mn=${encodeURIComponent(scope.mn)}`;
    }
    if (!isPost) {
      return axios.get(
        url,
        {
          params: {
            t: this.adapter.table, a: 'ca', sa: subAction, mod: module, req: request,
          },
        },
      );
    }

    return axios.post(url, {
      t: this.adapter.table, a: 'ca', sa: subAction, mod: module, req: request,
    });
  }
}

export default CustomAction;
