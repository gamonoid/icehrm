import React from 'react';
import {
  Alert, Col, DatePicker, TimePicker, Form, Input, Row, Tooltip, Slider,
} from 'antd';
import {
  InfoCircleOutlined,
} from '@ant-design/icons';
import moment from 'moment';
import ReactQuill, { Quill, Mixin, Toolbar } from 'react-quill';
import IceUpload from './IceUpload';
import IceDataGroup from './IceDataGroup';
import IceSelect from './IceSelect';
import IceLabel from './IceLabel';
import IceColorPick from './IceColorPick';
import IceSignature from './IceSignature';
import IceEditor from './IceEditor';


const ValidationRules = {

  float(str) {
    const floatstr = /^[-+]?[0-9]+(\.[0-9]+)?$/;
    if (str != null && str.match(floatstr)) {
      return true;
    }
    return false;
  },

  number(str) {
    const numstr = /^[0-9]+$/;
    if (str != null && str.match(numstr)) {
      return true;
    }
    return false;
  },

  numberOrEmpty(str) {
    if (str === '') {
      return true;
    }
    const numstr = /^[0-9]+$/;
    if (str != null && str.match(numstr)) {
      return true;
    }
    return false;
  },

  email(str) {
    const emailPattern = /^\s*[\w\-+_]+(\.[\w\-+_]+)*@[\w\-+_]+\.[\w\-+_]+(\.[\w\-+_]+)*\s*$/;
    return str != null && emailPattern.test(str);
  },

  emailOrEmpty(str) {
    if (str === '') {
      return true;
    }
    const emailPattern = /^\s*[\w\-+_]+(\.[\w\-+_]+)*@[\w\-+_]+\.[\w\-+_]+(\.[\w\-+_]+)*\s*$/;
    return str != null && emailPattern.test(str);
  },

  username(str) {
    const username = /^[a-zA-Z0-9.-]+$/;
    return str != null && username.test(str);
  },
  password(password) {
    if (password.length < 8) {
      return false;
    }

    if (password.length > 30) {
      return false;
    }

    const numberTester = /.*[0-9]+.*$/;
    if (!password.match(numberTester)) {
      return false;
    }

    const lowerTester = /.*[a-z]+.*$/;
    if (!password.match(lowerTester)) {
      return false;
    }

    const upperTester = /.*[A-Z]+.*$/;
    if (!password.match(upperTester)) {
      return false;
    }

    const symbolTester = /.*[\W]+.*$/;
    if (!password.match(symbolTester)) {
      return false;
    }

    return true;
  },
};


class IceForm extends React.Component {
  constructor(props) {
    super(props);
    this.validationRules = {};
    this.state = {
      validations: {},
      errorMsg: false,
    };
    this.formReference = React.createRef();
  }

  showError(errorMsg) {
    this.setState({ errorMsg });
  }

  hideError() {
    this.setState({ errorMsg: false });
  }

  isReady() {
    return this.formReference.current != null;
  }

  validateFields() {
    return this.formReference.current.validateFields();
  }

  render() {
    const { fields, twoColumnLayout, adapter } = this.props;
    let formInputs = [];
    const formInputs1 = [];
    const formInputs2 = [];
    const columns = !twoColumnLayout ? 1 : 2;
    for (let i = 0; i < fields.length; i++) {
      if (this.props.viewOnly && adapter.getViewModeEnabledFields() !== null
        && adapter.getViewModeEnabledFields().indexOf(fields[i][0]) === -1
      ) {
        continue;
      }

      formInputs.push(
        adapter.beforeRenderFieldHook(
          fields[i][0],
          this.createFromField(fields[i], this.props.viewOnly),
          fields[i][1],
        ),
      );
    }
    formInputs = formInputs.filter((input) => !!input);

    for (let i = 0; i < formInputs.length; i++) {
      if (formInputs[i] != null) {
        if (columns === 1) {
          formInputs1.push(formInputs[i]);
        } else if (i % 2 === 0) {
          formInputs1.push(formInputs[i]);
        } else {
          formInputs2.push(formInputs[i]);
        }
      }
    }

    const onFormLayoutChange = () => { };

    let layout = this.props.layout || 'horizontal';
    if (!this.props.layout) {
      layout = adapter.getFormLayout(this.props.viewOnly);
    }

    return (
      <Form
        ref={this.formReference}
        labelCol={{ span: 6 }}
        wrapperCol={{ span: layout === 'vertical' ? 24 : 16 }}
        layout={layout}
        initialValues={{ size: 'middle' }}
        onValuesChange={onFormLayoutChange}
        size="middle"
      >
        {this.state.errorMsg
          && (
            <>
              <Alert message={this.state.errorMsg} type="error" showIcon />
              <br />
            </>
          )}
        {columns === 1 && formInputs1}
        {columns === 2 && (
          <Row gutter={16}>
            <Col className="gutter-row" span={12}>
              {formInputs1}
            </Col>
            <Col className="gutter-row" span={12}>
              {formInputs2}
            </Col>
          </Row>
        )}
      </Form>
    );
  }

  isValid() {
    return Object.keys(this.validationRules).reduce((acc, fieldName) => acc && (this.state[fieldName] === 'success' || this.state[fieldName] == null), true);
  }

  validateOnChange(event) {
    const validationRule = this.validationRules[event.target.id];
    const { validations } = this.state;

    if (validationRule) {
      if (validationRule.rule(event.target.value)) {
        this.state[event.target.id] = 'success';
        this.state[`${event.target.id}_message`] = null;
      } else {
        this.state[event.target.id] = 'error';
        this.state[`${event.target.id}_message`] = validationRule.message;
      }
    }
    this.setState({ validations });
  }

  createFromField(field, viewOnly = false, showLabel = true) {
    let userId = 0;
    const rules = [];
    const requiredRule = { required: true };
    const [name, data] = field;
    const { adapter } = this.props;
    let { layout } = this.props;
    let validationRule = null;
    data.label = adapter.gt(data.label);

    viewOnly = viewOnly || (data.readonly === true);

    if (!layout) {
      layout = adapter.getFormLayout(this.props.viewOnly);
    }

    const labelSpan = layout === 'vertical' ? { span: 24 } : { span: 6 };

    const tempSelectBoxes = ['select', 'select2', 'select2multi'];
    if (tempSelectBoxes.indexOf(data.type) >= 0 && data['allow-null'] === true) {
      requiredRule.required = false;
    } else if (data.validation === 'none'
      || data.validation === 'emailOrEmpty'
      || data.validation === 'numberOrEmpty'
    ) {
      requiredRule.required = false;
    } else {
      requiredRule.required = true;
      requiredRule.message = this.generateFieldMessage(data.label);
    }

    if (viewOnly && adapter.getViewModeShowLabel() === false) {
      data.label = '';
    }

    rules.push(requiredRule);

    const label = (
      <div>
        {' '}
        {data.label}
        {' '}
        { data.help
        && (<Tooltip title={data.help}><InfoCircleOutlined style={{ fontSize: '16px', color: '#1890ff' }} /></Tooltip>)}
      </div>
    );

    if (data.type === 'hidden') {
      requiredRule.required = false;
      return (
        <Form.Item
          labelCol={labelSpan}
          style={{ display: 'none' }}
          label={label}
          key={name}
          name={name}
          rules={rules}
        >
          <Input />
        </Form.Item>
      );
    } if (data.type === 'text' || data.type === 'password') {
      if (data.validation) {
        // TODO - not sure why following line was there. This stop correct validations for rules like numberOrEmpty
        // data.validation = data.validation.replace('OrEmpty', '');
        validationRule = this.getValidationRule(data);
        if (validationRule) {
          this.validationRules[name] = {
            rule: validationRule,
            message: data.message ? data.message : `Invalid value for ${data.label}`,
          };
        }
      }
      if (validationRule != null) {
        return (
          <Form.Item
            labelCol={labelSpan}
            label={label}
            key={name}
            name={name}
            rules={rules}
            validateStatus={this.state[name]}
            help={this.state[`${name}_message`]}
          >
            {viewOnly
              ? <IceLabel />
              : data.type === 'password' ? <Input.Password onChange={this.validateOnChange.bind(this)} /> : <Input onChange={this.validateOnChange.bind(this)} />}
          </Form.Item>
        );
      }
      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
        >
          {viewOnly
            ? <IceLabel />
            : <Input />}
        </Form.Item>
      );
    } if (data.type === 'textarea') {
      if (!data.rows) {
        data.rows = 4;
      }
      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
        >
          {viewOnly
            ? <IceLabel />
            : <Input.TextArea rows={data.rows} />}
        </Form.Item>
      );
    } if (data.type === 'date') {
      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
        >
          <DatePicker disabled={viewOnly} />
        </Form.Item>
      );
    } if (data.type === 'datetime') {
      let dateFormat = 'YYYY-MM-DD HH:mm:ss';
      if (data.dateFormat) {
        dateFormat = data.dateFormat;
      }
      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
        >
          <DatePicker format={dateFormat} showTime disabled={viewOnly} />
        </Form.Item>
      );
    } if (data.type === 'time') {
      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
        >
          <TimePicker
            format="HH:mm"
            disabled={viewOnly}
          />
        </Form.Item>
      );
    } if (data.type === 'fileupload') {
      const currentEmployee = adapter.getCurrentProfile();
      if (currentEmployee != null) {
        userId = currentEmployee.id;
      } else {
        userId = adapter.getUser().id * -1;
      }

      if (data.filetypes == null) {
        data.filetypes = '.doc,.docx'
          + 'application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,'
          + 'image/*,'
          + '.pdf';
      }

      return (
        <Form.Item
          labelCol={labelSpan}
          name={name}
          key={name}
          label={label}
        >
          <IceUpload
            user={userId}
            fileGroup={adapter.tab}
            fileName={name}
            adapter={adapter}
            accept={data.filetypes}
            readOnly={viewOnly}
          />
        </Form.Item>
      );
    } if (data.type === 'datagroup') {
      return (
        <Form.Item
          labelCol={labelSpan}
          name={name}
          key={name}
          label={label}
        >
          <IceDataGroup
            adapter={adapter}
            field={field}
            title={label}
            readOnly={viewOnly}
          />
        </Form.Item>
      );
    } if (data.type === 'select2' || data.type === 'select' || data.type === 'select2multi') {
      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
        >
          <IceSelect
            adapter={adapter}
            field={field}
            readOnly={viewOnly}
          />
        </Form.Item>
      );
    } if (data.type === 'colorpick') {
      return (
        <Form.Item
          labelCol={labelSpan}
          name={name}
          key={name}
          label={label}
        >
          <IceColorPick
            adapter={adapter}
            field={field}
            title={label}
            readOnly={viewOnly}
          />
        </Form.Item>
      );
    } if (data.type === 'signature') {
      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
        >
          <IceSignature readOnly={viewOnly} />
        </Form.Item>
      );
    } if (data.type === 'placeholder') {
      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
          shouldUpdate
        >
          <Input
            bordered={false}
          />
        </Form.Item>
      );
    } if (data.type === 'editor') {
      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
          shouldUpdate
        >
          <IceEditor
            adapter={adapter}
            field={field}
            title={label}
            readOnly={viewOnly}
          />
        </Form.Item>
      );
    } if (data.type === 'quill') {
      const modules = {
        toolbar: [
          [{ header: '1' }, { header: '2' }],
          ['bold', 'italic', 'underline'],
          [{ list: 'ordered' }, { list: 'bullet' }],
        ],
        clipboard: {
          // toggle to add extra line breaks when pasting HTML:
          matchVisual: false,
        },
      };
      const formats = [
        'header',
        'bold', 'italic', 'underline',
        'list', 'bullet',
      ];

      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
          shouldUpdate
        >
          {viewOnly
            ? <IceLabel />
            : (
              <ReactQuill
                theme="snow"
                modules={modules}
                formats={formats}
              />
            )}

        </Form.Item>
      );
    } if (data.type === 'slider') {
      return (
        <Form.Item
          labelCol={labelSpan}
          label={label}
          key={name}
          name={name}
          rules={rules}
        >
          <Slider
            min={data.min}
            max={data.max}
            defaultValue={data.defaultValue ? data.defaultValue : 0}
          />
        </Form.Item>
      );
    }
    return null;
  }

  generateFieldMessage(label) {
    return `${label}: ${this.props.adapter.gt('is required')}`;
  }

  getValidationRule(data) {
    if (ValidationRules[data.validation] == null) {
      return null;
    }

    return ValidationRules[data.validation];
  }

  dataToFormFields(data, fields) {
    for (let i = 0; i < fields.length; i++) {
      const [key, formInputData] = fields[i];
      if (formInputData.type === 'date') {
        data[key] = data[key] ? moment(data[key], 'YYYY-MM-DD') : null;
      } else if (formInputData.type === 'datetime') {
        data[key] = data[key] ? moment(data[key], 'YYYY-MM-DD HH:mm:ss') : null;
      } else if (formInputData.type === 'time') {
        data[key] = data[key] ? moment(data[key], 'HH:mm') : null;
      }
    }

    return data;
  }

  formFieldsToData(params, fields) {
    for (let i = 0; i < fields.length; i++) {
      const [key, formInputData] = fields[i];
      if (formInputData.type === 'date') {
        params[key] = params[key] ? params[key].format('YYYY-MM-DD') : 'NULL';
      } else if (formInputData.type === 'datetime') {
        params[key] = params[key] ? params[key].format('YYYY-MM-DD HH:mm:ss') : 'NULL';
      } else if (formInputData.type === 'time') {
        params[key] = params[key] ? params[key].format('HH:mm') : 'NULL';
      } else if ((formInputData.type === 'select' || formInputData.type === 'select2') && params[key] == null) {
        params[key] = 'NULL';
      }
    }

    return params;
  }

  updateFields(data) {
    const { fields } = this.props;
    data = this.dataToFormFields(data, fields);
    this.formReference.current.resetFields();
    if (data == null) {
      return;
    }
    try {
      this.formReference.current.setFieldsValue(data);
    } catch (e) {
      console.log(e);
    }
  }

  resetFields() {
    this.formReference.current.resetFields();
  }

  setFieldsValue(data) {
    this.formReference.current.setFieldsValue(data);
  }

  save(params, success) {
    const { adapter, fields } = this.props;
    let values = params;
    values = adapter.forceInjectValuesBeforeSave(values);
    const msg = adapter.doCustomValidation(values);
    if (msg !== null) {
      this.showError(msg);
      return;
    }
    if (adapter.csrfRequired) {
      values.csrf = $(`#${adapter.getTableName()}Form`).data('csrf');
    }

    const id = (adapter.currentElement != null) ? adapter.currentElement.id : null;
    if (id != null && id !== '') {
      values.id = id;
    }
    values = this.formFieldsToData(values, fields);
    adapter.add(values, [], () => adapter.get([]), () => {
      this.formReference.current.resetFields();
      this.showError(false);
      success();
    });
  }
}

export default IceForm;
