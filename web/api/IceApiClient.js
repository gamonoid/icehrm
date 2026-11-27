const axios = require('axios');

class IceApiClient {
  constructor(baseUrl, token, clientBaseUrl, legacyApiWrapper = true) {
    this.baseUrl = baseUrl;
    this.token = token;
    this.clientBaseUrl = clientBaseUrl;
    this.legacyApiWrapper = legacyApiWrapper;
  }

  /**
   * @param endpoint {string} REST path, e.g. 'kudos/people'
   * @param config {object} optional; `params` are sent as query-string parameters
   */
  get(endpoint, config = {}) {
    const params = IceApiClient.queryParams(config.params);
    if (this.legacyApiWrapper) {
      const url = `${this.clientBaseUrl}api/index.php?token=${this.token}&method=get&url=/${endpoint}`;
      return axios.get(url, { params });
    }

    return axios.get(this.baseUrl + endpoint, {
      params,
      headers: {
        Authorization: `Bearer ${this.token}`,
      },
    });
  }

  // The legacy wrapper routes on token/method/url in the same query string, so a
  // caller's params must not override them.
  static queryParams(params) {
    if (!params) return undefined;
    const out = { ...params };
    delete out.token;
    delete out.method;
    delete out.url;
    return out;
  }

  post(endpoint, data) {
    if (this.legacyApiWrapper) {
      const url = `${this.clientBaseUrl}api/index.php?token=${this.token}&method=post&url=/${endpoint}`;
      return axios.post(url, data);
    }

    return axios.post(this.baseUrl + endpoint, {
      headers: {
        Authorization: `Bearer ${this.token}`,
      },
      data,
    });
  }

  delete(endpoint) {
    if (this.legacyApiWrapper) {
      const url = `${this.clientBaseUrl}api/index.php?token=${this.token}&method=delete&url=/${endpoint}`;
      return axios.get(url);
    }

    return axios.delete(this.baseUrl + endpoint, {
      headers: {
        Authorization: `Bearer ${this.token}`,
      },
    });
  }
}

export default IceApiClient;
