import axios from 'axios';
import IceResponse from './IceResponse';

class ExtensionController {
  constructor(extension, url) {
    this.extension = extension;
    this.url = url;
  }

  handleRequest(action, requestData) {
    const dataUrl = `${this.url}?a=ca&sa=${action}&mod=${encodeURIComponent(this.extension)}&req=${JSON.stringify(requestData)}`;
    return axios.post(dataUrl, {})
      .then((response) => new IceResponse(response.data.status, response.data.data))
      .catch((error) => new IceResponse('ERROR', error));
  }

  getApiClient() {
    return window.modJs.apiClient;
  }
}

export default ExtensionController;
