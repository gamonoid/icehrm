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
