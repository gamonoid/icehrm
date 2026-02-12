import React from 'react';
import { Form, Input, Select } from 'antd';

class IceCountryCityInput extends React.Component {
  constructor(props) {
    super(props);
    this.formRef = React.createRef();
  }

  render() {
    const { adapter, field, value, onChange, readOnly } = this.props;
    const [name, data] = field;

    // Get country options from adapter's fieldMasterData
    const countryKey = 'Country_name_name';
    let countryOptions = adapter.fieldMasterData[countryKey] || [];

    // Handle case where fieldMasterData returns an object instead of array
    if (!Array.isArray(countryOptions)) {
      countryOptions = Object.entries(countryOptions).map(([key, val]) => [key, val]);
    }

    const countrySelectOptions = countryOptions.map((option) => ({
      label: option[1],
      value: option[0],
    }));

    let countryValue = undefined;
    let cityValue = '';

    // Parse existing value from JSON
    if (value) {
      try {
        const parsed = JSON.parse(value);
        countryValue = parsed.country || undefined;
        cityValue = parsed.city || '';
      } catch (e) {
        // Backward compatibility: if not JSON, treat as city name
        cityValue = value;
      }
    }

    const updateLocation = (country, city) => {
      const locationData = { country, city };
      if (onChange) {
        onChange(JSON.stringify(locationData));
      }
    };

    return (
      <Form ref={this.formRef}>
        <Input.Group compact style={{ display: 'flex', gap: '0px' }}>
          <Form.Item
            name={`${name}_country`}
            noStyle
            initialValue={countryValue}
          >
            <Select
              showSearch
              placeholder="Select Country"
              optionFilterProp="children"
              filterOption={(input, option) =>
                option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0
              }
              options={countrySelectOptions}
              allowClear
              disabled={readOnly}
              onChange={(selectedCountry) => {
                const currentCity = this.formRef.current?.getFieldValue(`${name}_city`) || '';
                updateLocation(selectedCountry || '', currentCity);
              }}
              style={{ minWidth: '200px' }}
            />
          </Form.Item>
          <Form.Item
            name={`${name}_city`}
            noStyle
            initialValue={cityValue}
          >
            <Input
              placeholder="City"
              disabled={readOnly}
              onChange={(e) => {
                const currentCountry = this.formRef.current?.getFieldValue(`${name}_country`) || '';
                updateLocation(currentCountry, e.target.value);
              }}
              style={{ flex: 1 }}
            />
          </Form.Item>
        </Input.Group>
      </Form>
    );
  }

  componentDidUpdate(prevProps) {
    const { value, field } = this.props;
    const [name] = field;

    // Update form fields when value prop changes
    if (prevProps.value !== value && this.formRef.current) {
      let countryValue = undefined;
      let cityValue = '';

      if (value) {
        try {
          const parsed = JSON.parse(value);
          countryValue = parsed.country || undefined;
          cityValue = parsed.city || '';
        } catch (e) {
          cityValue = value;
        }
      }

      this.formRef.current.setFieldsValue({
        [`${name}_country`]: countryValue,
        [`${name}_city`]: cityValue,
      });
    }
  }
}

export default IceCountryCityInput;
