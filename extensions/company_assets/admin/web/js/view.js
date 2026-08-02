import React, { useState, useEffect, useMemo } from 'react';
import {
  Card, Table, Input, Select, Button, Space, Tag, DatePicker, TimePicker, Modal, Form,
  Typography, Empty, Popconfirm, Row, Col, Statistic, Tabs, message, Spin, InputNumber,
  Switch, Divider
} from 'antd';
import {
  SearchOutlined, PlusOutlined, EditOutlined, DeleteOutlined, SaveOutlined,
  LaptopOutlined, UserOutlined, TagOutlined, ToolOutlined, CheckCircleOutlined,
  CloseCircleOutlined, WarningOutlined, MinusCircleOutlined, SettingOutlined,
  AppstoreOutlined
} from '@ant-design/icons';
import moment from 'moment';

// Supported field types from IceForm
const FIELD_TYPES = [
  { value: 'text', label: 'Text' },
  { value: 'textarea', label: 'Text Area' },
  { value: 'date', label: 'Date' },
  { value: 'datetime', label: 'Date & Time' },
  { value: 'time', label: 'Time' },
  { value: 'select', label: 'Dropdown' },
  { value: 'switch', label: 'Yes/No Switch' },
];

// Custom Field Builder Component
const CustomFieldBuilder = ({ value = [], onChange }) => {
  const [fields, setFields] = useState(value || []);

  useEffect(() => {
    setFields(value || []);
  }, [value]);

  const handleFieldChange = (index, key, val) => {
    const newFields = [...fields];
    newFields[index] = { ...newFields[index], [key]: val };
    setFields(newFields);
    onChange && onChange(newFields);
  };

  const addField = () => {
    const newFields = [...fields, { name: '', label: '', type: 'text', required: false, options: [] }];
    setFields(newFields);
    onChange && onChange(newFields);
  };

  const removeField = (index) => {
    const newFields = fields.filter((_, i) => i !== index);
    setFields(newFields);
    onChange && onChange(newFields);
  };

  const handleOptionsChange = (index, optionsText) => {
    const options = optionsText.split('\n').filter(o => o.trim()).map(o => ({
      value: o.trim().toLowerCase().replace(/\s+/g, '_'),
      label: o.trim()
    }));
    handleFieldChange(index, 'options', options);
  };

  return (
    <div>
      <Divider orientation="left" style={{ marginTop: 0 }}>
        <SettingOutlined /> Custom Fields
      </Divider>
      {fields.map((field, index) => (
        <Card key={index} size="small" style={{ marginBottom: 8 }} bodyStyle={{ padding: 12 }}>
          <Row gutter={8}>
            <Col span={8}>
              <Form.Item label="Field Name" style={{ marginBottom: 4 }}>
                <Input
                  placeholder="field_name"
                  value={field.name}
                  onChange={(e) => handleFieldChange(index, 'name', e.target.value.toLowerCase().replace(/\s+/g, '_'))}
                />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label="Label" style={{ marginBottom: 4 }}>
                <Input
                  placeholder="Display Label"
                  value={field.label}
                  onChange={(e) => handleFieldChange(index, 'label', e.target.value)}
                />
              </Form.Item>
            </Col>
            <Col span={6}>
              <Form.Item label="Type" style={{ marginBottom: 4 }}>
                <Select
                  value={field.type}
                  onChange={(val) => handleFieldChange(index, 'type', val)}
                  style={{ width: '100%' }}
                >
                  {FIELD_TYPES.map(t => (
                    <Option key={t.value} value={t.value}>{t.label}</Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
            <Col span={2} style={{ display: 'flex', alignItems: 'center', paddingTop: 22 }}>
              <Button
                type="text"
                danger
                icon={<MinusCircleOutlined />}
                onClick={() => removeField(index)}
              />
            </Col>
          </Row>
          {field.type === 'select' && (
            <Row>
              <Col span={24}>
                <Form.Item label="Options (one per line)" style={{ marginBottom: 4 }}>
                  <Input.TextArea
                    rows={2}
                    placeholder="Option 1&#10;Option 2&#10;Option 3"
                    value={field.options?.map(o => o.label).join('\n') || ''}
                    onChange={(e) => handleOptionsChange(index, e.target.value)}
                  />
                </Form.Item>
              </Col>
            </Row>
          )}
          <Row>
            <Col span={24}>
              <Switch
                checked={field.required}
                onChange={(checked) => handleFieldChange(index, 'required', checked)}
                size="small"
              /> <Text type="secondary">Required field</Text>
            </Col>
          </Row>
        </Card>
      ))}
      <Button type="dashed" onClick={addField} block icon={<PlusOutlined />}>
        Add Custom Field
      </Button>
    </div>
  );
};

// Dynamic Custom Fields Renderer for Asset Form
const DynamicCustomFields = ({ customFields = [], value = {}, onChange }) => {
  const handleValueChange = (fieldName, val) => {
    const newValues = { ...value, [fieldName]: val };
    onChange && onChange(newValues);
  };

  if (!customFields || customFields.length === 0) {
    return null;
  }

  return (
    <>
      <Divider orientation="left">
        <SettingOutlined /> Custom Fields
      </Divider>
      <Row gutter={16}>
        {customFields.map((field) => {
          const fieldValue = value?.[field.name];

          if (field.type === 'text') {
            return (
              <Col span={12} key={field.name}>
                <Form.Item
                  label={field.label}
                  required={field.required}
                >
                  <Input
                    value={fieldValue}
                    onChange={(e) => handleValueChange(field.name, e.target.value)}
                    placeholder={field.label}
                  />
                </Form.Item>
              </Col>
            );
          }

          if (field.type === 'textarea') {
            return (
              <Col span={24} key={field.name}>
                <Form.Item
                  label={field.label}
                  required={field.required}
                >
                  <Input.TextArea
                    rows={2}
                    value={fieldValue}
                    onChange={(e) => handleValueChange(field.name, e.target.value)}
                    placeholder={field.label}
                  />
                </Form.Item>
              </Col>
            );
          }

          if (field.type === 'date') {
            return (
              <Col span={12} key={field.name}>
                <Form.Item
                  label={field.label}
                  required={field.required}
                >
                  <DatePicker
                    style={{ width: '100%' }}
                    value={fieldValue ? moment(fieldValue) : null}
                    onChange={(date) => handleValueChange(field.name, date ? date.format('YYYY-MM-DD') : null)}
                  />
                </Form.Item>
              </Col>
            );
          }

          if (field.type === 'datetime') {
            return (
              <Col span={12} key={field.name}>
                <Form.Item
                  label={field.label}
                  required={field.required}
                >
                  <DatePicker
                    showTime={{ format: 'HH:mm' }}
                    format="YYYY-MM-DD HH:mm"
                    style={{ width: '100%' }}
                    value={fieldValue ? moment(fieldValue) : null}
                    onChange={(date) => handleValueChange(field.name, date ? date.format('YYYY-MM-DD HH:mm') : null)}
                  />
                </Form.Item>
              </Col>
            );
          }

          if (field.type === 'time') {
            return (
              <Col span={12} key={field.name}>
                <Form.Item
                  label={field.label}
                  required={field.required}
                >
                  <TimePicker
                    format="HH:mm"
                    style={{ width: '100%' }}
                    value={fieldValue ? moment(fieldValue, 'HH:mm') : null}
                    onChange={(time) => handleValueChange(field.name, time ? time.format('HH:mm') : null)}
                  />
                </Form.Item>
              </Col>
            );
          }

          if (field.type === 'select') {
            return (
              <Col span={12} key={field.name}>
                <Form.Item
                  label={field.label}
                  required={field.required}
                >
                  <Select
                    value={fieldValue}
                    onChange={(val) => handleValueChange(field.name, val)}
                    placeholder={`Select ${field.label}`}
                    allowClear
                    style={{ width: '100%' }}
                  >
                    {(field.options || []).map((opt) => (
                      <Option key={opt.value} value={opt.value}>{opt.label}</Option>
                    ))}
                  </Select>
                </Form.Item>
              </Col>
            );
          }

          if (field.type === 'switch') {
            return (
              <Col span={12} key={field.name}>
                <Form.Item
                  label={field.label}
                  required={field.required}
                >
                  <Switch
                    checked={fieldValue === true || fieldValue === '1' || fieldValue === 1}
                    onChange={(checked) => handleValueChange(field.name, checked ? '1' : '0')}
                    checkedChildren="Yes"
                    unCheckedChildren="No"
                  />
                </Form.Item>
              </Col>
            );
          }

          return null;
        })}
      </Row>
    </>
  );
};

const { Text } = Typography;
const { Option } = Select;
const { TextArea } = Input;

const CompanyAssetsView = () => {
  const [activeTab, setActiveTab] = useState('assets');
  const [loading, setLoading] = useState(true);
  const [assets, setAssets] = useState([]);
  const [assetTypes, setAssetTypes] = useState([]);
  const [employees, setEmployees] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [stats, setStats] = useState(null);

  // Filters
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState(null);
  const [typeFilter, setTypeFilter] = useState(null);

  // Modal states
  const [isAssetModalVisible, setIsAssetModalVisible] = useState(false);
  const [isTypeModalVisible, setIsTypeModalVisible] = useState(false);
  const [editingAsset, setEditingAsset] = useState(null);
  const [editingType, setEditingType] = useState(null);
  const [saving, setSaving] = useState(false);

  // Custom fields state
  const [typeCustomFields, setTypeCustomFields] = useState([]);
  const [selectedTypeId, setSelectedTypeId] = useState(null);
  const [customFieldValues, setCustomFieldValues] = useState({});

  const [assetForm] = Form.useForm();
  const [typeForm] = Form.useForm();

  // Get custom fields for currently selected type
  const currentTypeCustomFields = useMemo(() => {
    if (!selectedTypeId) return [];
    const type = assetTypes.find(t => t.id === selectedTypeId);
    return type?.custom_fields || [];
  }, [selectedTypeId, assetTypes]);

  const getApiClient = () => window.modJs.apiClient;

  const fetchData = async () => {
    setLoading(true);
    try {
      const [assetsRes, typesRes, employeesRes, departmentsRes, statsRes] = await Promise.all([
        getApiClient().get('company_assets/assets'),
        getApiClient().get('company_assets/asset-types'),
        getApiClient().get('company_assets/employees'),
        getApiClient().get('company_assets/departments'),
        getApiClient().get('company_assets/stats'),
      ]);

      if (Array.isArray(assetsRes.data)) setAssets(assetsRes.data);
      if (Array.isArray(typesRes.data)) setAssetTypes(typesRes.data);
      if (Array.isArray(employeesRes.data)) setEmployees(employeesRes.data);
      if (Array.isArray(departmentsRes.data)) setDepartments(departmentsRes.data);
      if (statsRes.data) setStats(statsRes.data);
    } catch (error) {
      message.error('Failed to load data');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  // Asset handlers
  const handleAddAsset = () => {
    setEditingAsset(null);
    assetForm.resetFields();
    assetForm.setFieldsValue({ status: 'Available' });
    setSelectedTypeId(null);
    setCustomFieldValues({});
    setIsAssetModalVisible(true);
  };

  const handleEditAsset = (asset) => {
    setEditingAsset(asset);
    assetForm.resetFields();
    assetForm.setFieldsValue({
      id: asset.id,
      code: asset.code,
      name: asset.name,
      type: asset.type?.id,
      serial_number: asset.serial_number,
      status: asset.status,
      employee: asset.employee?.id,
      department: asset.department?.id,
      purchase_date: asset.purchase_date ? moment(asset.purchase_date) : null,
      purchase_price: asset.purchase_price,
      warranty_end: asset.warranty_end ? moment(asset.warranty_end) : null,
      location: asset.location,
      notes: asset.notes,
      description: asset.description,
    });
    setSelectedTypeId(asset.type?.id || null);
    setCustomFieldValues(asset.custom_field_values || {});
    setIsAssetModalVisible(true);
  };

  // Handle type selection change in asset form
  const handleAssetTypeChange = (typeId) => {
    setSelectedTypeId(typeId);
    // Clear custom field values when type changes
    if (typeId !== editingAsset?.type?.id) {
      setCustomFieldValues({});
    }
  };

  const handleSaveAsset = async () => {
    try {
      const values = await assetForm.validateFields();
      setSaving(true);

      const payload = {
        id: editingAsset?.id || null,
        code: values.code,
        name: values.name,
        type: values.type || null,
        serial_number: values.serial_number || null,
        status: values.status,
        employee: values.employee || null,
        department: values.department || null,
        purchase_date: values.purchase_date ? values.purchase_date.format('YYYY-MM-DD') : null,
        purchase_price: values.purchase_price || null,
        warranty_end: values.warranty_end ? values.warranty_end.format('YYYY-MM-DD') : null,
        location: values.location || null,
        notes: values.notes || null,
        description: values.description || null,
        custom_field_values: customFieldValues,
      };

      const response = await getApiClient().post('company_assets/assets', payload);

      if (response.data?.id) {
        message.success(editingAsset ? 'Asset updated' : 'Asset created');
        setIsAssetModalVisible(false);
        fetchData();
      } else {
        const errorMsg = response.data?.error?.[0]?.[0]?.message || response.data || 'Failed to save';
        message.error(errorMsg, 5);
      }
    } catch (error) {
      if (error.response?.data?.error?.[0]?.[0]?.message) {
        message.error(error.response.data.error[0][0].message, 5);
      } else {
        message.error('Please fill in the required fields');
      }
    } finally {
      setSaving(false);
    }
  };

  const handleDeleteAsset = async (id) => {
    try {
      await getApiClient().delete(`company_assets/assets/${id}`);
      message.success('Asset deleted');
      fetchData();
    } catch (error) {
      message.error('Failed to delete asset');
    }
  };

  // Asset Type handlers
  const handleAddType = () => {
    setEditingType(null);
    typeForm.resetFields();
    setTypeCustomFields([]);
    setIsTypeModalVisible(true);
  };

  const handleEditType = (type) => {
    setEditingType(type);
    typeForm.resetFields();
    typeForm.setFieldsValue({
      id: type.id,
      name: type.name,
      description: type.description,
    });
    setTypeCustomFields(type.custom_fields || []);
    setIsTypeModalVisible(true);
  };

  const handleSaveType = async () => {
    try {
      const values = await typeForm.validateFields();
      setSaving(true);

      const payload = {
        id: editingType?.id || null,
        name: values.name,
        description: values.description || null,
        custom_fields: typeCustomFields,
      };

      const response = await getApiClient().post('company_assets/asset-types', payload);

      if (response.data?.id) {
        message.success(editingType ? 'Asset type updated' : 'Asset type created');
        setIsTypeModalVisible(false);
        fetchData();
      } else {
        message.error('Failed to save asset type');
      }
    } catch (error) {
      message.error('Please fill in the required fields');
    } finally {
      setSaving(false);
    }
  };

  const handleDeleteType = async (id) => {
    try {
      const response = await getApiClient().delete(`company_assets/asset-types/${id}`);
      if (response.data?.error) {
        message.error(response.data.error[0]?.[0]?.message || 'Failed to delete', 5);
      } else {
        message.success('Asset type deleted');
        fetchData();
      }
    } catch (error) {
      const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to delete';
      message.error(errorMsg, 5);
    }
  };

  // Filtered assets
  const filteredAssets = assets.filter((a) => {
    if (searchTerm) {
      const search = searchTerm.toLowerCase();
      const matchSearch = (
        a.code?.toLowerCase().includes(search) ||
        a.name?.toLowerCase().includes(search) ||
        a.serial_number?.toLowerCase().includes(search) ||
        a.employee?.name?.toLowerCase().includes(search)
      );
      if (!matchSearch) return false;
    }
    if (statusFilter && a.status !== statusFilter) return false;
    if (typeFilter && a.type?.id !== typeFilter) return false;
    return true;
  });

  const getStatusColor = (status) => {
    switch (status) {
      case 'Available': return 'green';
      case 'Assigned': return 'blue';
      case 'In Repair': return 'orange';
      case 'Retired': return 'default';
      default: return 'default';
    }
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'Available': return <CheckCircleOutlined />;
      case 'Assigned': return <UserOutlined />;
      case 'In Repair': return <ToolOutlined />;
      case 'Retired': return <CloseCircleOutlined />;
      default: return null;
    }
  };

  // Asset table columns
  const assetColumns = [
    {
      title: 'Code',
      dataIndex: 'code',
      key: 'code',
      sorter: (a, b) => (a.code || '').localeCompare(b.code || ''),
      render: (text) => <strong>{text}</strong>,
    },
    {
      title: 'Name',
      dataIndex: 'name',
      key: 'name',
      sorter: (a, b) => (a.name || '').localeCompare(b.name || ''),
    },
    {
      title: 'Type',
      dataIndex: 'type',
      key: 'type',
      render: (type) => type ? <Tag icon={<TagOutlined />}>{type.name}</Tag> : '-',
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (status) => (
        <Tag color={getStatusColor(status)} icon={getStatusIcon(status)}>
          {status}
        </Tag>
      ),
    },
    {
      title: 'Assigned To',
      dataIndex: 'employee',
      key: 'employee',
      render: (emp) => emp ? (
        <Space>
          <UserOutlined />
          <span>{emp.name}</span>
        </Space>
      ) : <Text type="secondary">-</Text>,
    },
    {
      title: 'Serial Number',
      dataIndex: 'serial_number',
      key: 'serial_number',
      render: (text) => text || <Text type="secondary">-</Text>,
    },
    {
      title: 'Location',
      dataIndex: 'location',
      key: 'location',
      render: (text) => text || <Text type="secondary">-</Text>,
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 120,
      render: (_, record) => (
        <Space size="small">
          <Button
            type="text"
            size="small"
            icon={<EditOutlined />}
            onClick={() => handleEditAsset(record)}
          />
          <Popconfirm
            title="Delete this asset?"
            onConfirm={() => handleDeleteAsset(record.id)}
            okText="Yes"
            cancelText="No"
          >
            <Button type="text" size="small" danger icon={<DeleteOutlined />} />
          </Popconfirm>
        </Space>
      ),
    },
  ];

  // Asset type table columns
  const typeColumns = [
    {
      title: 'Name',
      dataIndex: 'name',
      key: 'name',
      sorter: (a, b) => (a.name || '').localeCompare(b.name || ''),
      render: (text) => <strong>{text}</strong>,
    },
    {
      title: 'Description',
      dataIndex: 'description',
      key: 'description',
      render: (text) => text || <Text type="secondary">-</Text>,
    },
    {
      title: 'Custom Fields',
      key: 'customFieldsCount',
      render: (_, record) => {
        const count = record.custom_fields?.length || 0;
        return count > 0 ? (
          <Tag color="blue" icon={<SettingOutlined />}>{count}</Tag>
        ) : <Text type="secondary">-</Text>;
      },
    },
    {
      title: 'Assets',
      key: 'assetCount',
      render: (_, record) => {
        const count = assets.filter(a => a.type?.id === record.id).length;
        return <Tag>{count}</Tag>;
      },
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 120,
      render: (_, record) => (
        <Space size="small">
          <Button
            type="text"
            size="small"
            icon={<EditOutlined />}
            onClick={() => handleEditType(record)}
          />
          <Popconfirm
            title="Delete this asset type?"
            onConfirm={() => handleDeleteType(record.id)}
            okText="Yes"
            cancelText="No"
          >
            <Button type="text" size="small" danger icon={<DeleteOutlined />} />
          </Popconfirm>
        </Space>
      ),
    },
  ];

  if (loading) {
    return (
      <div style={{ textAlign: 'center', padding: 50 }}>
        <Spin size="large" />
      </div>
    );
  }

  const tabItems = [
    {
      key: 'assets',
      label: (
        <span>
          <LaptopOutlined /> Company Assets ({assets.length})
        </span>
      ),
      children: (
        <div>
          {/* Stats */}
          {stats && (
            <Row gutter={16} style={{ marginBottom: 16 }}>
              <Col span={6}>
                <Card size="small">
                  <Statistic title="Total Assets" value={stats.total} prefix={<AppstoreOutlined />} />
                </Card>
              </Col>
              <Col span={6}>
                <Card size="small">
                  <Statistic
                    title="Available"
                    value={stats.available}
                    valueStyle={{ color: '#52c41a' }}
                    prefix={<CheckCircleOutlined />}
                  />
                </Card>
              </Col>
              <Col span={6}>
                <Card size="small">
                  <Statistic
                    title="Assigned"
                    value={stats.assigned}
                    valueStyle={{ color: '#1890ff' }}
                    prefix={<UserOutlined />}
                  />
                </Card>
              </Col>
              <Col span={6}>
                <Card size="small">
                  <Statistic
                    title="In Repair"
                    value={stats.inRepair}
                    valueStyle={{ color: '#faad14' }}
                    prefix={<ToolOutlined />}
                  />
                </Card>
              </Col>
            </Row>
          )}

          {/* Filters and Add button */}
          <Card size="small" style={{ marginBottom: 16 }}>
            <Space wrap>
              <Input
                placeholder="Search assets..."
                prefix={<SearchOutlined />}
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                style={{ width: 250 }}
                allowClear
              />
              <Select
                placeholder="Status"
                value={statusFilter}
                onChange={setStatusFilter}
                style={{ width: 150 }}
                allowClear
              >
                <Option value="Available">Available</Option>
                <Option value="Assigned">Assigned</Option>
                <Option value="In Repair">In Repair</Option>
                <Option value="Retired">Retired</Option>
              </Select>
              <Select
                placeholder="Asset Type"
                value={typeFilter}
                onChange={setTypeFilter}
                style={{ width: 180 }}
                allowClear
                showSearch
                filterOption={(input, option) =>
                  (option?.children ?? '').toLowerCase().includes(input.toLowerCase())
                }
              >
                {assetTypes.map((t) => (
                  <Option key={t.id} value={t.id}>{t.name}</Option>
                ))}
              </Select>
              <Button type="primary" icon={<PlusOutlined />} onClick={handleAddAsset}>
                Add Asset
              </Button>
            </Space>
          </Card>

          {/* Assets Table */}
          <Table
            dataSource={filteredAssets}
            columns={assetColumns}
            rowKey="id"
            size="small"
            pagination={{ pageSize: 15, showSizeChanger: true }}
            locale={{ emptyText: <Empty description="No assets found" /> }}
          />
        </div>
      ),
    },
    {
      key: 'types',
      label: (
        <span>
          <TagOutlined /> Asset Types ({assetTypes.length})
        </span>
      ),
      children: (
        <div>
          <Card size="small" style={{ marginBottom: 16 }}>
            <Space>
              <Button type="primary" icon={<PlusOutlined />} onClick={handleAddType}>
                Add Asset Type
              </Button>
            </Space>
          </Card>

          <Table
            dataSource={assetTypes}
            columns={typeColumns}
            rowKey="id"
            size="small"
            pagination={{ pageSize: 10 }}
            locale={{ emptyText: <Empty description="No asset types found" /> }}
          />
        </div>
      ),
    },
  ];

  return (
    <div style={{ padding: 0 }}>
      <Tabs activeKey={activeTab} onChange={setActiveTab} items={tabItems} />

      {/* Asset Modal */}
      <Modal
        title={editingAsset ? 'Edit Asset' : 'Add Asset'}
        open={isAssetModalVisible}
        onCancel={() => setIsAssetModalVisible(false)}
        width={700}
        footer={[
          <Button key="cancel" onClick={() => setIsAssetModalVisible(false)}>
            Cancel
          </Button>,
          <Button
            key="save"
            type="primary"
            icon={<SaveOutlined />}
            loading={saving}
            onClick={handleSaveAsset}
          >
            Save
          </Button>,
        ]}
      >
        <Form form={assetForm} layout="vertical" style={{ marginTop: 16 }}>
          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="id" hidden>
                <Input />
              </Form.Item>
              <Form.Item
                name="code"
                label="Asset Code"
                rules={[{ required: true, message: 'Please enter asset code' }]}
              >
                <Input placeholder="e.g., AST-001" />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="name" label="Asset Name">
                <Input placeholder="e.g., MacBook Pro 16" />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="type" label="Asset Type">
                <Select
                  placeholder="Select type"
                  allowClear
                  showSearch
                  filterOption={(input, option) =>
                    (option?.children ?? '').toLowerCase().includes(input.toLowerCase())
                  }
                  onChange={handleAssetTypeChange}
                >
                  {assetTypes.map((t) => (
                    <Option key={t.id} value={t.id}>{t.name}</Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="serial_number" label="Serial Number">
                <Input placeholder="Serial number" />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                name="status"
                label="Status"
                rules={[{ required: true, message: 'Please select status' }]}
              >
                <Select placeholder="Select status">
                  <Option value="Available">Available</Option>
                  <Option value="Assigned">Assigned</Option>
                  <Option value="In Repair">In Repair</Option>
                  <Option value="Retired">Retired</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="employee" label="Assigned To">
                <Select
                  placeholder="Select employee"
                  allowClear
                  showSearch
                  filterOption={(input, option) =>
                    (option?.children ?? '').toLowerCase().includes(input.toLowerCase())
                  }
                >
                  {employees.map((e) => (
                    <Option key={e.id} value={e.id}>{e.name}</Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="department" label="Department">
                <Select
                  placeholder="Select department"
                  allowClear
                  showSearch
                  filterOption={(input, option) =>
                    (option?.children ?? '').toLowerCase().includes(input.toLowerCase())
                  }
                >
                  {departments.map((d) => (
                    <Option key={d.id} value={d.id}>{d.title}</Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="location" label="Location">
                <Input placeholder="e.g., Office Building A, Floor 2" />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={8}>
              <Form.Item name="purchase_date" label="Purchase Date">
                <DatePicker style={{ width: '100%' }} />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item name="purchase_price" label="Purchase Price">
                <InputNumber
                  style={{ width: '100%' }}
                  placeholder="0.00"
                  precision={2}
                  min={0}
                />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item name="warranty_end" label="Warranty End">
                <DatePicker style={{ width: '100%' }} />
              </Form.Item>
            </Col>
          </Row>

          <Form.Item name="notes" label="Notes">
            <TextArea rows={2} placeholder="Additional notes" />
          </Form.Item>

          <Form.Item name="description" label="Description">
            <TextArea rows={2} placeholder="Asset description" />
          </Form.Item>

          {/* Dynamic Custom Fields based on selected type */}
          <DynamicCustomFields
            customFields={currentTypeCustomFields}
            value={customFieldValues}
            onChange={setCustomFieldValues}
          />
        </Form>
      </Modal>

      {/* Asset Type Modal */}
      <Modal
        title={editingType ? 'Edit Asset Type' : 'Add Asset Type'}
        open={isTypeModalVisible}
        onCancel={() => setIsTypeModalVisible(false)}
        width={700}
        footer={[
          <Button key="cancel" onClick={() => setIsTypeModalVisible(false)}>
            Cancel
          </Button>,
          <Button
            key="save"
            type="primary"
            icon={<SaveOutlined />}
            loading={saving}
            onClick={handleSaveType}
          >
            Save
          </Button>,
        ]}
      >
        <Form form={typeForm} layout="vertical" style={{ marginTop: 16 }}>
          <Form.Item name="id" hidden>
            <Input />
          </Form.Item>
          <Form.Item
            name="name"
            label="Type Name"
            rules={[{ required: true, message: 'Please enter type name' }]}
          >
            <Input placeholder="e.g., Laptop, Monitor, Keyboard" />
          </Form.Item>
          <Form.Item name="description" label="Description">
            <TextArea rows={3} placeholder="Description of this asset type" />
          </Form.Item>

          <CustomFieldBuilder
            value={typeCustomFields}
            onChange={setTypeCustomFields}
          />
        </Form>
      </Modal>
    </div>
  );
};

export default CompanyAssetsView;
