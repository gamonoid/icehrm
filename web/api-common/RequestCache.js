/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/**
 * RequestCache
 */

class MemoryStorage {
  constructor() {
    this.data = {};
  }

  getItem(key) {
    return this.data[key];
  }

  setItem(key, data) {
    this.data[key] = data;
  }

  removeAllByPrefix(prefix) {
    const keys = Object.keys(this.data);
    for (let i = 0; i < keys.length; i++) {
      if (keys[i].indexOf(prefix) > 0) {
        delete this.data[keys[i]];
      }
    }
  }
}


class RequestCache {
  constructor(storage) {
    if (!storage) {
      this.storage = new MemoryStorage();
    } else {
      this.storage = storage;
    }
  }

  getKey(url, params) {
    let key = `${url}|`;
    for (const index in params) {
      key += `${index}=${params[index]}|`;
    }
    return key;
  }

  /*
  invalidateTable(table) {
    let key;
    for (let i = 0; i < this.storage.length; i++) {
      key = this.storage.key(i);
      if (key.indexOf(`t=${table}`) > 0) {
        this.storage.removeItem(key);
      }
    }
  }
  */

  invalidateTable(table) {
    this.storage.removeAllByPrefix(`t=${table}`);
  }


  getData(key) {
    const data = this.storage.getItem(key);
    if (!data) {
      return null;
    }

    return data;
  }

  setData(key, data) {

    if (data.status !== undefined && data.status != null && data.status !== 'SUCCESS') {
      return null;
    }
    this.storage.setItem(key, data);
    return data;
  }
}

export default RequestCache;
