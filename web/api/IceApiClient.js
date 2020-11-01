const axios = require('axios');

class IceApiClient {
  constructor(baseUrl, token) {
    this.baseUrl = baseUrl;
    this.token = token;
  }

  get(endpoint) {
    return axios.get(this.baseUrl + endpoint, {
      headers: {
        Authorization: `Bearer ${this.token}`,
      },
    });
  }
}

export default IceApiClient;
