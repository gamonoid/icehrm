const axios = require('axios');

class IceApiClient {
  constructor(baseUrl, token, clientBaseUrl, legacyApiWrapper = true) {
    this.baseUrl = baseUrl;
    this.token = token;
    this.clientBaseUrl = clientBaseUrl;
    this.legacyApiWrapper = legacyApiWrapper;
  }

  get(endpoint) {
    if (this.legacyApiWrapper) {
      const url = `${this.clientBaseUrl}api/index.php?token=${this.token}&method=get&url=/${endpoint}`;
      return axios.get(url);
    }

    return axios.get(this.baseUrl + endpoint, {
      headers: {
        Authorization: `Bearer ${this.token}`,
      },
    });
  }
}

export default IceApiClient;
