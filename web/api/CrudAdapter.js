import React from 'react';
import {
  Modal
} from 'antd';
import {
  ExclamationCircleOutlined
} from "@ant-design/icons";
const qs = require('qs');
const axios = require('axios');

class CrudAdapter {
  constructor(adapter) {
    this.adapter = adapter;
  }

  delete(id, callback) {
    const that = this;

    const deleteConfirm = () => {
      this.adapter.requestCache.invalidateTable(that.adapter.table);

      const data = {
        t: that.adapter.table,
        a: 'delete', id
      };
      // Using qs because when calling service.php axios should send parameters as formData (not in JSON body)
      axios.post(that.adapter.moduleRelativeURL, qs.stringify(data))
        .then((data) => {
          if (data.data.status === 'SUCCESS') {
            callback();
            that.adapter.trackEvent('delete', that.adapter.tab, that.adapter.table);
          } else {
            Modal.error({
              title: 'Error',
              content: `Error deleting item: ${data.data.data}`,
              okText: 'Ok',
            });
          }

        }).catch ((e) => {
          Modal.error({
            title: 'Error',
            content: `${e.message}. ${e.response.data.message}`,
            okText: 'Ok',
          });
        });
    };

    Modal.confirm({
      title: 'Confirm',
      icon: <ExclamationCircleOutlined />,
      content: 'Do you want to delete this item?',
      okText: 'Delete',
      cancelText: 'Cancel',
      onOk: deleteConfirm,
    });
  }
}

export default CrudAdapter;