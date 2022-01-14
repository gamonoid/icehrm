import React from "react";
import { Button, Select, Space, Card, Table } from "antd";
// import IceDataGroupModal from "./IceDataGroupModal";
import IceFormModal from "./IceFormModal";
import ReactDOM from "react-dom";
const { Option } = Select;

class IceDataGroup extends React.Component {
  state = {};

  constructor(props) {
    super(props);
    this.onChange = props.onChange;
    this.formReference = React.createRef();
  }

  render() {
    const { field, adapter } = this.props;
    let { value } = this.props;

    value = this.parseValue(value);
    value = value.map(item => ({ ...item, key: item.id }));
    const columns = JSON.parse(JSON.stringify(field[1].columns));

    if (!this.props.readOnly) {
      columns.push({
        title: 'Action',
        key: 'action',
        render: (text, record) => (
          this.getDefaultButtons(record.id)
        ),
      });
    }

    return (
      <>
        <div id="dataGroupContainer"></div>
        {!this.props.readOnly &&
          <Space direction="horizontal">
            <Button type="link" htmlType="button" onClick={() => {
              this.createForm(field, adapter, {})
            }}>
              Add
          </Button>
            <Button type="link" htmlType="button" danger onClick={() => {
              this.resetDataGroup()
            }}>
              Reset
          </Button>
          </Space>
        }
        <Table columns={columns} dataSource={value} />
      </>
    );
  }

  createForm(field, adapter, object) {
    this.formContainer = React.createRef();
    const formFields = field[1].form;
    formFields.unshift(['id', { label: 'ID', type: 'hidden' }]);
    ReactDOM.render(
      <IceFormModal
        ref={this.formContainer}
        fields={formFields}
        title={this.props.title}
        adapter={adapter}
        formReference={this.formReference}
        saveCallback={this.save.bind(this)}
        cancelCallback={this.unmountForm.bind(this)}
      />,
      document.getElementById('dataGroupContainer'),
    );
    this.formContainer.current.show(object);
  }

  unmountForm() {
    ReactDOM.unmountComponentAtNode(document.getElementById('dataGroupContainer'));
  }

  show(data) {
    if (!data) {
      this.setState({ visible: true });
      this.updateFields(data);
    } else {
      this.setState({ visible: true });
      if (this.formReference.current) {
        this.updateFields(data);
      } else {
        this.waitForIt(
          () => this.formReference.current != null,
          () => { this.updateFields(data); },
          100,
        );
      }
    }
  }

  parseValue(value) {
    try {
      value = JSON.parse(value);
    } catch (e) {
      value = [];
    }
    if (value == null) {
      value = [];
    }
    return value;
  }

  save(params, errorCallback, closeCallback) {
    const { field, value } = this.props;

    if (field[1]['custom-validate-function'] != null) {
      let tempParams = field[1]['custom-validate-function'].apply(this, [params]);
      if (tempParams.valid) {
        params = tempParams.params;
      } else {
        errorCallback(tempParams.message);
        return false;
      }
    }

    const data = this.parseValue(value);

    let newData = [];
    if (!params.id) {
      params.id = `${field[0]}_${this.dataGroupGetNextAutoIncrementId(data)}`;
      data.push(params);
      newData = data;
    } else {
      for (let i = 0; i < data.length; i++) {
        const item = data[i];
        if (item.id !== params.id) {
          newData.push(item);
        } else {
          newData.push(params);
        }
      }
    }

    if (field[1]['sort-function'] != null) {
      newData.sort(field[1]['sort-function']);
    }

    const val = JSON.stringify(newData);

    this.onChange(val);

    this.unmountForm();
  }

  createCard(item) {
    const { field } = this.props;
    if (field[1]['pre-format-function'] != null) {
      item = field[1]['pre-format-function'].apply(this, [item]);
    }

    const template = field[1].html;
    let t = template.replace('#_delete_#', '');
    t = t.replace('#_edit_#', '');
    t = t.replace(/#_id_#/g, item.id);

    for (const key in item) {
      let itemVal = item[key];
      if (itemVal !== undefined && itemVal != null && typeof itemVal === 'string') {
        itemVal = itemVal.replace(/(?:\r\n|\r|\n)/g, '<br />');
      }
      t = t.replace(`#_${key}_#`, itemVal);
    }

    if (field[1].render !== undefined && field[1].render != null) {
      t = t.replace('#_renderFunction_#', field[1].render(item));
    }

    return (
      <Card key={item.id} title="" extra={this.getDefaultButtons(item.id)}>
        <div dangerouslySetInnerHTML={{ __html: t }}></div>
      </Card>
    );
  }

  getDefaultButtons(id) {
    return (
      <Space>
        <a href="#" onClick={() => { this.editDataGroupItem(id) }}><li className="fa fa-edit" /></a>
        <a href="#" onClick={() => { this.deleteDataGroupItem(id) }}><li className="fa fa-times" /></a>
      </Space>
    );
  }

  deleteDataGroupItem(id) {
    const { value } = this.props;
    const data = this.parseValue(value);

    const newVal = [];

    for (let i = 0; i < data.length; i++) {
      const item = data[i];
      if (item.id !== id) {
        newVal.push(item);
      }
    }

    const val = JSON.stringify(newVal);

    this.onChange(val);
  }

  editDataGroupItem(id) {
    const { field, adapter, value } = this.props;
    const data = this.parseValue(value);

    let editVal = {};

    for (let i = 0; i < data.length; i++) {
      const item = data[i];
      if (item.id === id) {
        editVal = item;
      }
    }

    this.createForm(field, adapter, editVal);
  }

  resetDataGroup() {
    this.onChange('[]');
  }

  dataGroupGetNextAutoIncrementId(data) {
    let autoId = 1; let id;
    for (let i = 0; i < data.length; i++) {
      const item = data[i];
      if (item.id === undefined || item.id == null) {
        item.id = 1;
      }
      id = item.id.substring(item.id.lastIndexOf('_') + 1, item.id.length);
      if (id >= autoId) {
        autoId = parseInt(id, 10) + 1;
      }
    }

    return autoId;
  }

}

export default IceDataGroup;
