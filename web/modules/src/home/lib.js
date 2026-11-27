import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';

/**
 * Minimal adapter for the Home module. Home has no entity/table of its own; this
 * exists so the SPA shell can wire the module context (translations, urls, the
 * REST api client) exactly as it does for every other natively mounted module —
 * the view then reads window.modJs.apiClient. Mirrors the `connection` module.
 */
class HomeAdapter extends ReactModalAdapterBase {
  constructor(data) {
    super('', '', '', '');
    this.data = data || {};
  }

  // Nothing to list: the view fetches appshell/home itself.
  get() {}
}

export { HomeAdapter };
