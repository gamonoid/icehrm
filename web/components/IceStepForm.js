import React from 'react';
import {
  Button, Divider, Steps, Row, Col, Space,
} from 'antd';
import IceForm from './IceForm';

const { Step } = Steps;

class IceStepForm extends IceForm {
  constructor(props) {
    super(props);

    this.onChange = props.onChange;

    let steps = this.props.fields.map((item) => ({
      ...item,
      ref: React.createRef(),
    }));

    steps = steps.map((item) => {
      const { ref, fields } = item;
      item.content = (
        <IceForm
          ref={ref}
          adapter={props.adapter}
          fields={fields}
          twoColumnLayout={props.twoColumnLayout}
          width={props.width}
          layout={props.layout || 'horizontal'}
        />
      );

      return item;
    });

    this.state = {
      current: 0,
      steps,
      loading: false,
    };
  }

  moveToStep(current) {
    this.setState({ current });
  }

  next() {
    if (this.validateFields(false) === false) {
      return;
    }

    this.showError(false);
    const current = this.state.current + 1;
    this.setState({ current });
  }

  prev() {
    const current = this.state.current - 1;
    if (current < 0) {
      return;
    }
    this.setState({ current });
  }

  render() {
    const { adapter } = this.props;
    const { current, steps } = this.state;
    return (
      <>
        <Steps current={current}>
          {steps.map((item, index) => (
            <Step key={item.title} title={item.title} onClick={() => this.moveToStep(index)} />
          ))}
        </Steps>
        <Divider />
        <div className="steps-content">
          {steps.map((item, index) => (
            <div style={{ display: index === current ? 'block' : 'none' }}>
              {item.content}
            </div>
          ))}
        </div>
        <Divider />
        <div className="steps-action">
          <Row gutter={16}>
            <Col className="gutter-row" span={12} style={{}}>
              <Space>
                {current < steps.length - 1 && (
                  <Button type="primary" onClick={() => this.next()}>
                    {adapter.gt('Next')}
                  </Button>
                )}
                {current > 0 && (
                  <Button onClick={() => this.prev()}>
                    {adapter.gt('Previous')}
                  </Button>
                )}
              </Space>
            </Col>
            <Col className="gutter-row" span={12} style={{ textAlign: 'right' }}>
              <Space>
                <Button onClick={() => this.props.closeModal()}>
                  {adapter.gt('Cancel')}
                </Button>
                <Button type="primary" loading={this.state.loading} onClick={() => this.saveData()}>
                  {adapter.gt('Save')}
                </Button>
              </Space>
            </Col>
          </Row>
        </div>
      </>
    );
  }

  async saveData() {
    this.setState({ loading: true });
    const data = await this.validateFields(true);
    if (data) {
      this.save(data, () => this.props.closeModal());
    } else {
      this.setState({ loading: false });
    }
  }

  save(params, success, fail) {
    const { adapter } = this.props;
    adapter.add(params, [], () => adapter.get([]), () => {
      this.resetFields();
      this.showError(false);
      success();
      this.setState({ loading: false });
    }, () => {
      this.setState({ loading: false });
      if (fail) { fail() };
    },);
  }

  updateFields(data) {
    this.state.steps.forEach((item) => {
      const subData = {};
      item.fields.forEach(([key]) => {
        subData[key] = data[key];
      });
      this.updateFieldsSubForm(item.ref, item.fields, subData);
    });
  }

  updateFieldsSubForm(ref, fields, data) {
    data = this.dataToFormFields(data, fields);
    ref.current.resetFields();
    if (data == null) {
      return;
    }
    try {
      ref.current.setFieldsValue(data);
    } catch (e) {
      console.log(e);
    }
  }

  async validateFields(all) {
    const { adapter } = this.props;
    const steps = all ? this.state.steps : this.state.steps.slice(0, this.state.current + 1);
    const promiseList = steps.map(
      (item) => item.ref.current.validateFields()
        .then((values) => {
          if (!item.ref.current.isValid()) {
            return false;
          }
          return values;
        })
        .catch(() => false),
    );

    const allData = await Promise.all(promiseList);
    const failedIndex = allData.findIndex((item) => item === false);

    if (failedIndex >= 0) {
      this.setState({ current: failedIndex });

      return false;
    }

    let values = Object.assign({}, ...allData);
    values = adapter.forceInjectValuesBeforeSave(values);
    const msg = adapter.doCustomValidation(values);

    if (msg !== null) {
      this.showError(msg);
      return false;
    }

    if (adapter.csrfRequired) {
      values.csrf = $(`#${adapter.getTableName()}Form`).data('csrf');
    }

    const id = (adapter.currentElement != null) ? adapter.currentElement.id : null;
    if (id != null && id !== '') {
      values.id = id;
    }

    const fields = [].concat.apply([], this.state.steps.map((item) => item.fields));

    return this.formFieldsToData(values, fields);
  }

  getSubFormData(ref, fields, params) {
    const { adapter } = this.props;
    let values = params;
    values = adapter.forceInjectValuesBeforeSave(values);
    const msg = adapter.doCustomValidation(values);
    if (msg !== null) {
      ref.current.showError(msg);
      return;
    }

    if (adapter.csrfRequired) {
      values.csrf = $(`#${adapter.getTableName()}Form`).data('csrf');
    }

    const id = (adapter.currentElement != null) ? adapter.currentElement.id : null;
    if (id != null && id !== '') {
      values.id = id;
    }

    return this.formFieldsToData(values, fields);
  }

  showError(errorMsg) {
    this.state.steps.forEach((item) => item.ref.current.showError(errorMsg));
  }

  resetFields() {
    this.state.steps.forEach((item) => item.ref.current.resetFields());
  }

  hideError() {
    this.state.steps.forEach((item) => item.ref.current.hideError());
  }

  isReady() {
    return this.state.steps.reduce((acc, item) => acc && item.ref.current != null, true);
  }
}

export default IceStepForm;
