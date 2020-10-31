import React from "react";
import {Button, message, Space, Upload, Tag} from "antd";
import { UploadOutlined, DownloadOutlined, DeleteOutlined } from '@ant-design/icons';

class IceUpload extends React.Component {
  state = {
    fileList: [],
    uploaded: false,
  };

  _isMounted = false;

  constructor(props) {
    super(props);
    this.onChange = props.onChange;
  }

  componentDidMount() {
    this._isMounted = true;
    message.config({
      top: 55,
      duration: 2,
    });
  }

  componentWillUnmount() {
    this._isMounted = false;
  }

  handleDelete = () => {
    this.setState({ fileList: [], value: null, uploaded: false});
    this.onChange(null);
  };

  handleView = () => {
    let currentValue = this.props.value;
    if (this.state.value != null && this.state.value !== '') {
      currentValue = this.state.value;
    }
    if (currentValue == null || currentValue === '') {
      message.error('File not found');
      return;
    }

    const { adapter } = this.props;

    adapter.getFile(currentValue)
      .then((data) => {
        const file = {
          key: data.uid,
          uid: data.uid,
          name: data.name,
          status: data.status,
          url: data.filename,
        };
        window.open(file.url);
      }).catch((e) => {

    });
  };

  handleChange = info => {
    let fileList = [...info.fileList];

    if (fileList.length === 0) {
      this.setState({ value: null });
      this.onChange(null);
      this.setState({fileList: []});
      this.setState({uploaded: false});
      return;
    }

    fileList = fileList.slice(-1);

    if (fileList[0].response && fileList[0].response.status === 'error') {
      this.setState({ value: null });
      this.onChange(null);
      this.setState({fileList: []});
      this.setState({uploaded: false});
      message.error(`Error: ${fileList[0].response.message}`);
      return;
    }

    fileList = fileList.map(file => {
      if (file.response) {
        // Component will show file.url as link
        file.name = file.response.name;
        file.url = file.response.url;
      }
      return file;
    });

    this.setState({fileList});
    this.setState({ value: this.getFileName(fileList), uploaded: true });
    this.onChange(this.getFileName(fileList));
  };

  getFileName(fileList) {
    let file = null;
    if (fileList) {
      file = fileList[0];
    }

    return file ? file.name : '';
  }

  generateRandom(length) {
    const d = new Date();
    const chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    let result = '';
    for (let i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result + d.getTime();
  }

  render() {
    let fileName = this.generateRandom(14);

    const props = {
      action: `${window.CLIENT_BASE_URL}fileupload-new.php?user=${this.props.user}&file_group=${this.props.fileGroup}&file_name=${fileName}`,
      onChange: this.handleChange,
      onRemove: this.handleDelete,
      multiple: false,
      listType: 'picture',
    };

    return (
      <Space direction={'vertical'}>
        {!this.props.readOnly &&
        <Space>
          <Upload {...props} fileList={this.state.fileList}>
            <Tag color="blue" style={{ cursor: 'pointer' }}>
              <UploadOutlined />
              {' '}
              Upload
            </Tag>
          </Upload>
        </Space>
        }
        <Space>
          { (((this.props.value != null && this.props.value !== '') || (this.state.value != null && this.state.value !== '')) && !this.state.uploaded) &&
          <Button type="link" htmlType="button" onClick={this.handleView}>
            <DownloadOutlined/> View File
          </Button>
          }
          { (((this.props.value != null && this.props.value !== '') || (this.state.value != null && this.state.value !== '')) && !this.state.uploaded && !this.props.readOnly) &&
          <Button type="link" htmlType="button" danger onClick={this.handleDelete}>
            <DeleteOutlined/> Delete
          </Button>
          }
        </Space>
      </Space>

    );
  }
}

export default IceUpload;
