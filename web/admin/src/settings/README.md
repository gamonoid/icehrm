# Settings Module - Ant Design Components

This directory contains React components for rendering IceHrm settings using Ant Design 5.24.3 components.

## Components

### SettingsField
Renders a single setting field based on meta JSON configuration.

**Supported Field Types:**
- `text` - Text input (Ant Design `Input`)
- `textarea` - Multi-line text input (Ant Design `Input.TextArea`)
- `select` - Dropdown with static options (Ant Design `Select`)
- `select2` - Searchable dropdown (Ant Design `Select` with `showSearch`)
- `select2multi` - Multi-select dropdown (Ant Design `Select` with `mode="multiple"`)
- `placeholder` - Read-only placeholder field

**Remote Source Support:**
- Supports `remote-source` configuration for loading data from backend models
- Automatically handles data loading and formatting

### SettingsForm
Modal form component for editing settings.

**Features:**
- Parses meta JSON to determine field type
- Displays setting description as help text
- Handles form validation
- Integrates with adapter for saving

## Usage

### Basic Usage

```javascript
import { SettingsForm } from './components';

// In your adapter
renderForm(setting) {
  const formContainer = React.createRef();
  
  ReactDOM.render(
    <SettingsForm
      ref={formContainer}
      adapter={this}
      onSave={this.handleSave.bind(this)}
    />,
    document.getElementById('formContainer')
  );
  
  formContainer.current.show(setting);
}

handleSave(values, callback) {
  const data = {
    t: this.table,
    a: 'save',
    id: values.id,
    value: values.value,
  };
  
  this.save(data, callback);
}
```

### Using SettingsField Directly

```javascript
import { SettingsField } from './components';

// In a form
<SettingsField
  fieldName="value"
  fieldConfig={{
    label: "Timezone",
    type: "select2",
    "remote-source": ["Timezone", "name", "details"]
  }}
  value={setting.value}
  adapter={adapter}
  onChange={(fieldName, value) => {
    // Handle change
  }}
/>
```

## Meta JSON Format

The `meta` column in the Settings table contains JSON that defines how the field is rendered:

```json
["value", {
  "label": "Setting Label",
  "type": "select2",
  "remote-source": ["ModelName", "idField", "displayField"]
}]
```

**Field Types:**
- `text` - Simple text input
- `textarea` - Multi-line text
- `select` - Dropdown with static `source` array
- `select2` - Searchable dropdown
- `select2multi` - Multi-select dropdown
- `placeholder` - Read-only display

**Options:**
- `source` - Array of `[value, label]` pairs for static options
- `remote-source` - Array `[ModelName, idField, displayField]` for dynamic loading
- `allow-null` - Boolean, allows null/empty selection
- `label` - Field label text

## Integration with Existing System

The components are designed to work with the existing `AdapterBase` and `ReactModalAdapterBase` classes. They:
- Use `adapter.fieldMasterData` for remote data
- Call `adapter.getFieldValues()` for loading remote sources
- Follow the same data format conventions

## Examples

### Static Options (select)
```json
["value", {
  "label": "Status",
  "type": "select",
  "source": [["1", "Yes"], ["0", "No"]]
}]
```

### Remote Source (select2)
```json
["value", {
  "label": "Country",
  "type": "select2",
  "remote-source": ["Country", "id", "name"]
}]
```

### Multi-Select with Remote Source
```json
["value", {
  "label": "Currencies",
  "type": "select2multi",
  "remote-source": ["CurrencyType", "id", "code+name"]
}]
```

### Time Select (select2 with time options)
```json
["value", {
  "label": "Value",
  "type": "select2",
  "source": [["00:00", "00:00"], ["00:15", "00:15"], ...]
}]
```
