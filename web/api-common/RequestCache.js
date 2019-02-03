/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/**
 * RequestCache
 */

class RequestCache {
  getKey(url, params) {
    let key = `${url}|`;
    for (const index in params) {
      key += `${index}=${params[index]}|`;
    }
    return key;
  }

  invalidateTable(table) {
    let key;
    for (let i = 0; i < localStorage.length; i++) {
      key = localStorage.key(i);
      if (key.indexOf(`t=${table}`) > 0) {
        localStorage.removeItem(key);
      }
    }
  }


  getData(key) {
    let data;

    if (typeof (Storage) === 'undefined') {
      return null;
    }

    const strData = localStorage.getItem(key);
    if (strData !== undefined && strData != null && strData !== '') {
      data = JSON.parse(strData);
      if (data === undefined || data == null) {
        return null;
      }

      if (data.status !== undefined && data.status != null && data.status !== 'SUCCESS') {
        return null;
      }

      return data;
    }

    return null;
  }

  setData(key, data) {
    if (typeof (Storage) === 'undefined') {
      return null;
    }

    if (data.status !== undefined && data.status != null && data.status !== 'SUCCESS') {
      return null;
    }

    const strData = JSON.stringify(data);
    localStorage.setItem(key, strData);
    return strData;
  }
}

export default RequestCache;
