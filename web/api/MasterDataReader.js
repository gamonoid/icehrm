const axios = require('axios');

class MasterDataReader {
  constructor(adapter) {
    this.adapter = adapter;
    this.requestCache = this.adapter.requestCache;
  }

  fetchMasterData(fieldMaster) {
    let method = '';
    let methodParams = '';
    if (fieldMaster[3] != null) {
      method = fieldMaster[3];
    }

    if (fieldMaster[4] != null) {
      methodParams = JSON.stringify(fieldMaster[4]);
    }

    const key = this.requestCache.getKey(this.adapter.moduleRelativeURL, {
      t: fieldMaster[0], key: fieldMaster[1], value: fieldMaster[2], method, methodParams, a: 'getFieldValues',
    });

    const cacheData = this.requestCache.getData(key);
    if (cacheData != null && cacheData.status === 'SUCCESS') {
      return new Promise((resolve, reject) => resolve(cacheData.data));
    }

    const urlData = {
      t: fieldMaster[0],
      key: fieldMaster[1],
      value: fieldMaster[2],
      method,
      methodParams,
      a: 'getFieldValues',
    };
    let url = `${this.adapter.moduleRelativeURL}?_url=1`;
    for (const index in urlData) {
      url = `${url}&${index}=${encodeURIComponent(urlData[index])}`;
    }
    // TODO - Should be a get request
    return axios.post(url, {})
      .then((response) => {
        if (response.data.status !== 'SUCCESS') {
          throw Error(`Response for ${key} failed`);
        }
        this.requestCache.setData(key, response.data);

        return response.data.data;
      });
  }

  updateAllMasterData() {
    const remoteSourceFields = this.adapter.getRemoteSourceFields();
    const promiseList = [];
    for (let i = 0; i < remoteSourceFields.length; i++) {
      const fieldRemote = remoteSourceFields[i];
      if (fieldRemote[1]['remote-source'] !== undefined && fieldRemote[1]['remote-source'] != null) {
        let key = `${fieldRemote[1]['remote-source'][0]}_${fieldRemote[1]['remote-source'][1]}_${fieldRemote[1]['remote-source'][2]}`;
        if (fieldRemote[1]['remote-source'].length === 4) {
          key = `${key}_${fieldRemote[1]['remote-source'][3]}`;
        }
        const masterDataPromise = this.fetchMasterData(fieldRemote[1]['remote-source'])
          .then((data) => {
            this.adapter.fieldMasterData[key] = data;
          });
        promiseList.push(masterDataPromise);
      }
    }

    return Promise.all(promiseList);
  }
}

export default MasterDataReader;
