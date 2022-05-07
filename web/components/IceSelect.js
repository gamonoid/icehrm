import React from 'react';
import { Form, Select } from 'antd';

const { Option } = Select;

class IceSelect extends React.Component {
  constructor(props) {
    super(props);
    this.onChange = props.onChange;
  }

  render() {
    let options;
    const { field, adapter } = this.props;
    let { value } = this.props;
    const data = field[1];
    if (data['remote-source'] != null) {
      let key = `${data['remote-source'][0]}_${data['remote-source'][1]}_${data['remote-source'][2]}`;
      if (data['remote-source'].length === 4) {
        key = `${key}_${data['remote-source'][3]}`;
      }
      options = adapter.fieldMasterData[key];
    } else {
      options = data.source;
    }
    const optionData = this.getFormSelectOptionsRemote(options, field, adapter);

    // value should be an array if multi-select
    if (data.type === 'select2multi') {
      try {
        value = JSON.parse(value);
        if (value == null) {
          value = [];
        }
        value = value.map((item) => `${item}`);
      } catch (e) {
        value = [];
      }
    } else {
      value = value ? value.toString() : value;
    }

    return (
      <Select
        mode={data.type === 'select2multi' ? 'multiple' : undefined}
        showSearch
        placeholder={`Select ${data.label}`}
        optionFilterProp="children"
        filterOption={
          (input, option) => input != null
            && option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0
        }
        value={value}
        options={optionData}
        allowClear
        onChange={this.handleChange.bind(this)}
        disabled={this.props.readOnly}
      />
    );
  }

  handleChange(value) {
    const { field } = this.props;
    const data = field[1];
    if (data.type === 'select2multi') {
      this.onChange(JSON.stringify(value));
    } else {
      this.onChange(value);
    }
  }

  makeOption(option) {
    return <Option key={`${option[0]}`} value={`${option[0]}`}>{option[1]}</Option>;
  }

  getFormSelectOptionsRemote(options, field, adapter) {
    const optionData = [];

    if (Array.isArray(options)) {
      for (let i = 0; i < options.length; i++) {
        optionData.push({
          label: options[i][1],
          value: options[i][0],
        });
      }
    } else {
      for (const key in options) {
        optionData.push({
          label: options[key],
          value: key,
        });
      }
    }

    // if (field[1].sort === 'true') {
    //   tuples.sort((a, b) => {
    //     a = a[1];
    //     b = b[1];
    //
    //     // eslint-disable-next-line no-nested-ternary
    //     return a < b ? -1 : (a > b ? 1 : 0);
    //   });
    // }

    // for (let i = 0; i < tuples.length; i++) {
    //   const prop = tuples[i][0];
    //   const value = tuples[i][1];
    //   optionData.push([prop, adapter.gt(value)]);
    // }

    return optionData;
  }
}

export default IceSelect;
