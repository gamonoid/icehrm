/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import {
  Form, Card, Button, Space, message, Spin, Alert, Typography, Input,
} from 'antd';
import {
  SaveOutlined, SearchOutlined,
} from '@ant-design/icons';
import axios from 'axios';
import IceApiClient from '../../../../api/IceApiClient';
import IceDataPipe from '../../../../api/IceDataPipe';
import SettingTextField from './SettingTextField';
import SettingTextareaField from './SettingTextareaField';
import SettingSelectField from './SettingSelectField';
import SettingMultiSelectField from './SettingMultiSelectField';
import SettingSwitchField from './SettingSwitchField';
import SettingPlaceholderField from './SettingPlaceholderField';
import SettingFileUploadField from './SettingFileUploadField';

const { Title } = Typography;

/**
 * SettingsPage Component
 * Renders all settings as form items based on their meta configuration
 */
class SettingsPage extends React.Component {
  constructor(props) {
    super(props);
    this.formRef = React.createRef();
    this.state = {
      settings: [],
      loading: false,
      saving: false,
      changedSettings: {}, // Track which settings have been changed
      searchQuery: '', // Search query for filtering settings
    };
  }

  componentDidMount() {
    // Load remote data first
    const { adapter } = this.props;
    if (adapter) {
      adapter.loadRemoteDataForSettings();
    }
    this.loadSettings();
  }

  componentDidUpdate(prevProps) {
    const { filter, adapter } = this.props;
    // Reload settings when filter changes (e.g., switching tabs)
    const prevFilter = prevProps.filter;
    const filterChanged = JSON.stringify(prevFilter) !== JSON.stringify(filter);
    
    if (filterChanged) {
      // Load remote data when filter changes
      if (adapter) {
        adapter.loadRemoteDataForSettings();
      }
      this.loadSettings();
    }
  }

  loadSettings = () => {
    const { adapter, filter: filterProp, category } = this.props;
    if (!adapter) {
      return;
    }

    this.setState({ loading: true });

    // Determine filter to use (handles array categories for "Other")
    // Prefer the parsed filter prop, then try adapter.filter (may need parsing), then category prop
    let filter = filterProp || null;
    
    // If filter prop is not available, try adapter.filter
    if (!filter && adapter.filter) {
      try {
        // Parse if it's a string, otherwise use as-is
        filter = typeof adapter.filter === 'string' ? JSON.parse(adapter.filter) : adapter.filter;
      } catch (e) {
        // If parsing fails, try to use as object
        filter = adapter.filter;
      }
    }
    
    // Final fallback: use category prop
    if (!filter && category) {
      filter = { category };
    }

    // IceDataPipe.getDataUrl() uses adapter.getFilter(), so we need to set it on the adapter
    // Set the filter on the adapter so dataPipe can use it
    if (filter) {
      adapter.setFilter(filter);
    }

    // Ensure dataPipe is initialized (it might not be set on first load)
    if (!adapter.dataPipe) {
      adapter.setDataPipe(new IceDataPipe(adapter));
    }

    // Use dataPipe to get settings data
    if (adapter.dataPipe) {
      adapter.dataPipe.get({
        page: 1,
        limit: 1000, // Get all settings
        filters: filter,
      })
        .then((response) => {
          // Debug: log response to check if data is being returned
          if (!response || !response.items) {
            console.log('Invalid response structure for filter:', filter, 'Response:', response);
            this.setState({
              settings: [],
              loading: false,
              changedSettings: {},
            });
            return;
          }
          
          if (response.items.length === 0) {
            console.log('No settings returned for filter:', filter, 'Response:', response);
          }
          
          // First, parse meta for all settings
          const settingsWithMeta = response.items.map((setting) => {
            // Parse meta JSON
            let metaConfig = null;
            if (setting.meta && setting.meta !== '') {
              try {
                const metaArray = JSON.parse(setting.meta);
                if (Array.isArray(metaArray) && metaArray.length >= 2) {
                  metaConfig = metaArray[1]; // Second element contains config
                }
              } catch (e) {
                console.error(`Error parsing meta for setting ${setting.name}:`, e);
              }
            }

            return {
              ...setting,
              metaConfig,
            };
          });

          // Sort settings by setting_order: non-zero values first (ascending), then zero values
          // Handle NULL values as 0 (default)
          const sortedSettings = settingsWithMeta.sort((a, b) => {
            // Parse setting_order, defaulting to 0 if null/undefined/empty
            const orderA = a.setting_order != null && a.setting_order !== '' 
              ? parseInt(a.setting_order, 10) 
              : 0;
            const orderB = b.setting_order != null && b.setting_order !== '' 
              ? parseInt(b.setting_order, 10) 
              : 0;
            
            // If both are 0, maintain original order (doesn't matter which comes first)
            if (orderA === 0 && orderB === 0) {
              return 0;
            }
            
            // If A is 0, it should go after B
            if (orderA === 0) {
              return 1;
            }
            
            // If B is 0, it should go after A
            if (orderB === 0) {
              return -1;
            }
            
            // Both are non-zero, sort ascending
            return orderA - orderB;
          });

          // Fetch full values for settings that appear truncated (postProcessGetData truncates to 30 chars)
          // Only fetch full values for settings that end with "..." (indicating truncation)
          const fullValuePromises = sortedSettings.map((setting) => {
            // Check if value appears truncated:
            // - Has a value
            // - Is a string
            // - Ends with "..." (truncation marker)
            // - Length is > 30 (truncation happens for values > 30 chars)
            const appearsTruncated = setting.value && 
              typeof setting.value === 'string' && 
              setting.value.endsWith('...') &&
              setting.value.length > 30;
            
            // If not truncated, return as-is
            if (!appearsTruncated) {
              return Promise.resolve(setting);
            }

            // Fetch full value using getElement (uses postProcessGetElement which doesn't truncate)
            return new Promise((resolve) => {
              const sourceMappingJson = JSON.stringify(adapter.getSourceMapping());
              const url = adapter.moduleRelativeURL || (typeof baseUrl !== 'undefined' ? baseUrl : '');
              
              if (typeof axios !== 'undefined') {
                axios.post(url, {
                  t: adapter.table,
                  a: 'getElement',
                  id: setting.id,
                  sm: sourceMappingJson,
                })
                  .then((response) => {
                    if (response.data && response.data.status === 'SUCCESS' && response.data.object) {
                      resolve({
                        ...setting,
                        value: response.data.object.value, // Full value without truncation
                      });
                    } else {
                      resolve(setting); // Fallback to truncated value
                    }
                  })
                  .catch(() => {
                    resolve(setting); // Fallback to truncated value on error
                  });
              } else if (typeof $ !== 'undefined') {
                $.post(url, {
                  t: adapter.table,
                  a: 'getElement',
                  id: setting.id,
                  sm: sourceMappingJson,
                }, (data) => {
                  if (data && data.status === 'SUCCESS' && data.object) {
                    resolve({
                      ...setting,
                      value: data.object.value, // Full value without truncation
                    });
                  } else {
                    resolve(setting); // Fallback to truncated value
                  }
                }, 'json')
                  .fail(() => {
                    resolve(setting); // Fallback to truncated value on error
                  });
              } else {
                resolve(setting); // Fallback if no HTTP client available
              }
            });
          });

          // Wait for all full values to be fetched
          Promise.all(fullValuePromises)
            .then((settings) => {
              // Filter out hidden settings
              const visibleSettings = settings.filter((setting) => {
                const hiddenSettings = [
                  'System: Do not pass JSON in request',
                  'Api: REST Api Token',
                ];
                return !hiddenSettings.includes(setting.name);
              });

              this.setState({
                settings: visibleSettings,
                loading: false,
                changedSettings: {},
              });

              // Set form values
              if (this.formRef.current) {
                const formValues = {};
                settings.forEach((setting) => {
                  const { type, source } = setting.metaConfig || {};
                  
                  // Check if this is a Switch field (Yes/No select)
                  const isYesNoSelect = (type === 'select' || type === 'select2') && source && Array.isArray(source);
                  const isSwitchField = isYesNoSelect
                    && source.length === 2
                    && source.every((item) => Array.isArray(item) && item.length >= 2)
                    && source.some((item) => String(item[0]) === '1' && String(item[1]).toLowerCase() === 'yes')
                    && source.some((item) => String(item[0]) === '0' && String(item[1]).toLowerCase() === 'no');
                  
                  // Convert Switch field values to boolean for Form.Item with valuePropName="checked"
                  if (isSwitchField) {
                    formValues[`setting_${setting.id}`] = setting.value === '1' || setting.value === 1 || setting.value === true;
                  } else if (type === 'select2multi') {
                    // For select2multi, parse JSON string to array, or use empty array if value is empty
                    try {
                      if (!setting.value || setting.value === '' || setting.value === null || setting.value === undefined) {
                        formValues[`setting_${setting.id}`] = [];
                      } else {
                        const parsed = JSON.parse(setting.value);
                        formValues[`setting_${setting.id}`] = Array.isArray(parsed) 
                          ? parsed.filter((item) => item !== null && item !== undefined && item !== '').map((item) => String(item))
                          : [];
                      }
                    } catch (e) {
                      formValues[`setting_${setting.id}`] = [];
                    }
                  } else {
                    formValues[`setting_${setting.id}`] = setting.value;
                  }
                });
                this.formRef.current.setFieldsValue(formValues);
              }
            });
        })
        .catch((error) => {
          console.error('Error loading settings:', error);
          this.setState({ loading: false });
          message.error('Failed to load settings');
        });
    } else {
      // Fallback: use direct API call
      const filterJson = filter ? JSON.stringify(filter) : '';
      const url = `${adapter.moduleRelativeURL.replace('service.php', 'data.php')}?t=${adapter.table}&ft=${encodeURIComponent(filterJson)}&version=v2`;
      
      axios.post(url, {})
        .then((response) => {
          if (response.data && response.data.status === 'SUCCESS') {
            const settings = response.data.objects.map((setting) => {
              let metaConfig = null;
              if (setting.meta && setting.meta !== '') {
                try {
                  const metaArray = JSON.parse(setting.meta);
                  if (Array.isArray(metaArray) && metaArray.length >= 2) {
                    metaConfig = metaArray[1];
                  }
                } catch (e) {
                  console.error(`Error parsing meta for setting ${setting.name}:`, e);
                }
              }

              return {
                ...setting,
                metaConfig,
              };
            });

            this.setState({
              settings,
              loading: false,
              changedSettings: {},
            });

            if (this.formRef.current) {
              const formValues = {};
              settings.forEach((setting) => {
                formValues[`setting_${setting.id}`] = setting.value;
              });
              this.formRef.current.setFieldsValue(formValues);
            }
          } else {
            this.setState({ loading: false });
            message.error('Failed to load settings');
          }
        })
        .catch((error) => {
          console.error('Error loading settings:', error);
          this.setState({ loading: false });
          message.error('Failed to load settings');
        });
    }
  };

  handleSettingChange = (settingId, newValue) => {
    const { changedSettings, settings } = this.state;
    
    // Find the setting to check field type
    const setting = settings.find((s) => s.id === settingId);
    const { type, source } = setting?.metaConfig || {};
    
    // Check if this is a Switch field (Yes/No select)
    const isYesNoSelect = (type === 'select' || type === 'select2') && source && Array.isArray(source);
    const isSwitchField = isYesNoSelect
      && source.length === 2
      && source.every((item) => Array.isArray(item) && item.length >= 2)
      && source.some((item) => String(item[0]) === '1' && String(item[1]).toLowerCase() === 'yes')
      && source.some((item) => String(item[0]) === '0' && String(item[1]).toLowerCase() === 'no');
    
    // For select2multi, newValue is already a JSON string from SettingMultiSelectField
    // We need to parse it back to an array for the form, but keep the JSON string for saving
    let formValue = newValue;
    let saveValue = newValue;
    
    if (type === 'select2multi') {
      // newValue is already a JSON string (from SettingMultiSelectField's onChange)
      // Parse it to array for form display
      try {
        if (typeof newValue === 'string') {
          formValue = JSON.parse(newValue);
          // Ensure it's an array and filter out invalid values
          if (Array.isArray(formValue)) {
            formValue = formValue
              .filter((item) => item !== null && item !== undefined && item !== '')
              .map((item) => String(item));
            // Remove duplicates
            formValue = [...new Set(formValue)];
            // Update saveValue to be the cleaned JSON string
            saveValue = JSON.stringify(formValue);
          } else {
            formValue = [];
            saveValue = JSON.stringify([]);
          }
        } else if (Array.isArray(newValue)) {
          // If it's already an array (shouldn't happen, but handle it)
          formValue = newValue
            .filter((item) => item !== null && item !== undefined && item !== '')
            .map((item) => String(item));
          formValue = [...new Set(formValue)]; // Remove duplicates
          saveValue = JSON.stringify(formValue);
        } else {
          formValue = [];
          saveValue = JSON.stringify([]);
        }
      } catch (e) {
        formValue = [];
        saveValue = JSON.stringify([]);
      }
    } else if (isSwitchField) {
      // For Switch fields, convert "1"/"0" string to boolean for form
      formValue = (newValue === '1' || newValue === 1 || newValue === true);
      saveValue = newValue; // Keep as "1"/"0" string for saving
    }
    
    // Update form value immediately
    if (this.formRef.current) {
      this.formRef.current.setFieldsValue({
        [`setting_${settingId}`]: formValue,
      });
    }
    
    this.setState({
      changedSettings: {
        ...changedSettings,
        [settingId]: saveValue, // Store the value to save (JSON string for select2multi, "1"/"0" for Switch)
      },
    });
  };

  handleSave = () => {
    const { changedSettings, settings } = this.state;
    const { adapter } = this.props;

    if (Object.keys(changedSettings).length === 0) {
      message.info('No changes to save');
      return;
    }

    this.setState({ saving: true });

    // Get API client from adapter or window.modJs
    let apiClient = null;
    if (adapter && adapter.apiClient) {
      apiClient = adapter.apiClient;
    } else if (typeof window !== 'undefined' && window.modJs && window.modJs.apiClient) {
      apiClient = window.modJs.apiClient;
    } else {
      // Fallback: try to get from global modJsList
      const adapterName = adapter ? adapter.tab : null;
      if (adapterName && typeof window !== 'undefined' && window.modJsList && window.modJsList[`tab${adapterName}`]) {
        apiClient = window.modJsList[`tab${adapterName}`].apiClient;
      }
    }

    // If no API client found, create one using legacy wrapper
    if (!apiClient) {
      const clientBaseUrl = typeof window !== 'undefined' ? window.CLIENT_BASE_URL : '';
      const apiUrl = adapter && adapter.apiUrl ? adapter.apiUrl : '';
      const token = adapter && adapter.apiToken ? adapter.apiToken : '';
      apiClient = new IceApiClient(apiUrl, token, clientBaseUrl, true);
    }

    // Save each changed setting using REST API endpoint via IceApiClient
    const savePromises = Object.keys(changedSettings).map((settingIdStr) => {
      // Convert string key to number for comparison
      const settingId = parseInt(settingIdStr, 10);
      const setting = settings.find((s) => s.id === settingId);
      if (!setting) {
        return Promise.resolve();
      }

      const requestData = {
        id: settingId,
        value: changedSettings[settingIdStr],
      };

      // Use IceApiClient to make the API call
      return apiClient.post('settings/save', requestData)
        .then((response) => {
          if (response.data && response.status === 200) {
            return Promise.resolve();
          } else {
            const errorMsg = response.data?.error?.[0]?.message 
              || response.data?.message 
              || response.data?.data 
              || 'Save failed';
            return Promise.reject(new Error(errorMsg));
          }
        })
        .catch((e) => {
          const errorMsg = e.response?.data?.error?.[0]?.message 
            || e.response?.data?.message 
            || e.response?.data?.data 
            || e.message 
            || 'Save failed';
          return Promise.reject(new Error(errorMsg));
        });
    });

    Promise.all(savePromises)
      .then(() => {
        message.success('Settings saved successfully');
        this.setState({
          changedSettings: {},
          saving: false,
        });
        // Reload settings to get updated values
        this.loadSettings();
      })
      .catch((error) => {
        message.error(`Error saving settings: ${error.message}`);
        this.setState({ saving: false });
      });
  };

  renderSettingField = (setting) => {
    const { adapter } = this.props;
    const { changedSettings } = this.state;
    const currentValue = changedSettings[setting.id] !== undefined
      ? changedSettings[setting.id]
      : setting.value;

    // Determine which field component to render
    let fieldComponent = null;

    if (!setting.metaConfig) {
      // Fallback to text field if meta is not configured
      fieldComponent = (
        <SettingTextField
          key={setting.id}
          setting={setting}
          value={currentValue}
          onChange={this.handleSettingChange}
          adapter={adapter}
        />
      );
    } else {
      const { type, source } = setting.metaConfig;

      // Check if it's a Yes/No select that should be a Switch
      // Yes/No selects have source like: [["1","Yes"],["0","No"]] or [["0","No"],["1","Yes"]]
      const isYesNoSelect = (type === 'select' || type === 'select2') && source && Array.isArray(source);
      const hasYesNoOptions = isYesNoSelect
        && source.length === 2
        && source.every((item) => Array.isArray(item) && item.length >= 2)
        && source.some((item) => String(item[0]) === '1' && String(item[1]).toLowerCase() === 'yes')
        && source.some((item) => String(item[0]) === '0' && String(item[1]).toLowerCase() === 'no');

      // Render based on type
      switch (type) {
        case 'text':
          fieldComponent = (
            <SettingTextField
              key={setting.id}
              setting={setting}
              value={currentValue}
              onChange={this.handleSettingChange}
              adapter={adapter}
            />
          );
          break;

        case 'textarea':
          fieldComponent = (
            <SettingTextareaField
              key={setting.id}
              setting={setting}
              value={currentValue}
              onChange={this.handleSettingChange}
              adapter={adapter}
            />
          );
          break;

        case 'select':
        case 'select2':
          // Use Switch for Yes/No selects
          if (hasYesNoOptions) {
            fieldComponent = (
              <SettingSwitchField
                key={setting.id}
                setting={setting}
                value={currentValue}
                onChange={this.handleSettingChange}
                adapter={adapter}
              />
            );
          } else {
            fieldComponent = (
              <SettingSelectField
                key={setting.id}
                setting={setting}
                value={currentValue}
                onChange={this.handleSettingChange}
                adapter={adapter}
              />
            );
          }
          break;

        case 'select2multi':
          fieldComponent = (
            <SettingMultiSelectField
              key={setting.id}
              setting={setting}
              value={currentValue}
              onChange={this.handleSettingChange}
              adapter={adapter}
            />
          );
          break;

        case 'placeholder':
          fieldComponent = (
            <SettingPlaceholderField
              key={setting.id}
              setting={setting}
              value={currentValue}
              adapter={adapter}
            />
          );
          break;

        case 'fileupload':
          fieldComponent = (
            <SettingFileUploadField
              key={setting.id}
              setting={setting}
              value={currentValue}
              onChange={this.handleSettingChange}
              adapter={adapter}
            />
          );
          break;

        default:
          // Fallback to text field
          fieldComponent = (
            <SettingTextField
              key={setting.id}
              setting={setting}
              value={currentValue}
              onChange={this.handleSettingChange}
              adapter={adapter}
            />
          );
          break;
      }
    }

    // Wrap field in Card with description below
    return (
      <Card
        key={setting.id}
        style={{ marginBottom: 16 }}
        bodyStyle={{ padding: '16px 24px' }}
      >
        {fieldComponent}
        {setting.description && (
          <Typography.Text style={{ fontSize: '13px', display: 'block', marginTop: '4px', color: 'rgba(0, 0, 0, 0.65)' }}>
            {setting.description}
          </Typography.Text>
        )}
      </Card>
    );
  };

  handleSearchChange = (e) => {
    this.setState({ searchQuery: e.target.value });
  };

  render() {
    const {
      settings, loading, saving, changedSettings, searchQuery,
    } = this.state;
    const { filter, adapter } = this.props;

    // Filter settings based on search query
    const filteredSettings = settings.filter((setting) => {
      if (!searchQuery || searchQuery.trim() === '') {
        return true;
      }
      const query = searchQuery.toLowerCase();
      const nameMatch = setting.name && setting.name.toLowerCase().includes(query);
      const descMatch = setting.description && setting.description.toLowerCase().includes(query);
      return nameMatch || descMatch;
    });
    
    // Get category name for display
    let categoryName = 'Settings';
    if (filter && filter.category) {
      if (Array.isArray(filter.category)) {
        categoryName = 'Other Settings';
      } else {
        categoryName = `${filter.category} Settings`;
      }
    }

    if (loading) {
      return (
        <div style={{ textAlign: 'center', padding: '50px' }}>
          <Spin size="large" />
        </div>
      );
    }

    const hasChanges = Object.keys(changedSettings).length > 0;

    return (
      <div style={{ padding: '24px' }}>
        <Card
          title={categoryName}
          extra={
            <Space>
              {hasChanges && (
                <Alert
                  message={`${Object.keys(changedSettings).length} setting(s) modified`}
                  type="info"
                  showIcon
                  style={{ marginRight: 16 }}
                />
              )}
              <Button
                type="primary"
                icon={<SaveOutlined />}
                onClick={this.handleSave}
                loading={saving}
                disabled={!hasChanges}
              >
                Save Changes
              </Button>
            </Space>
          }
        >
          {/* Search input */}
          <div style={{ marginBottom: 16 }}>
            <Input
              placeholder="Search settings by name or description..."
              prefix={<SearchOutlined />}
              value={searchQuery}
              onChange={this.handleSearchChange}
              allowClear
              style={{ maxWidth: 400 }}
            />
          </div>

          <Form
            ref={this.formRef}
            layout="vertical"
            onFinish={this.handleSave}
          >
            {settings.length === 0 ? (
              <Alert
                message="No settings found"
                type="info"
                showIcon
              />
            ) : filteredSettings.length === 0 ? (
              <Alert
                message={`No settings match "${searchQuery}"`}
                type="info"
                showIcon
              />
            ) : (
              filteredSettings.map((setting) => this.renderSettingField(setting))
            )}
          </Form>
        </Card>
      </div>
    );
  }
}

export default SettingsPage;
