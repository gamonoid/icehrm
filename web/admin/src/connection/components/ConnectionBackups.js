import React from 'react';
import {
  Card, Table, Button, Modal, Input, Form, message, Space, Tag, Popconfirm, Typography, Alert, Tooltip,
} from 'antd';
import {
  DatabaseOutlined, DownloadOutlined, DeleteOutlined, LockOutlined, ReloadOutlined, FileZipOutlined,
} from '@ant-design/icons';

const { Text, Paragraph } = Typography;

// The Connection adapter (window.modJs) is wired by the shell with a REST
// apiClient (bearer token) — reuse it for the backup endpoints.
const api = () => (window.modJs && window.modJs.apiClient) || null;

const humanSize = (bytes) => {
  const b = Number(bytes);
  if (!b || Number.isNaN(b)) return '-';
  const units = ['B', 'KB', 'MB', 'GB'];
  let i = 0;
  let v = b;
  while (v >= 1024 && i < units.length - 1) { v /= 1024; i += 1; }
  return `${v.toFixed(v >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
};

const parseMeta = (meta) => {
  if (!meta) return {};
  try { return JSON.parse(meta); } catch (e) { return {}; }
};

// Success responses return the payload directly; errors come back as
// { error: [[{ message }]] } (see RestEndPoint::sendResponse).
const errorOf = (res) => {
  const d = res && res.data;
  if (d && d.error) return (d.error[0] && d.error[0][0] && d.error[0][0].message) || 'Request failed';
  return null;
};
const httpErrorOf = (error) => (
  error && error.response && error.response.data && error.response.data.error
    && error.response.data.error[0] && error.response.data.error[0][0]
    && error.response.data.error[0][0].message
) || null;

class ConnectionBackups extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      backups: [],
      loading: false,
      modalOpen: false,
      creating: false,
      name: '',
      password: '',
      confirmPassword: '',
      filesModalOpen: false,
      creatingFiles: false,
      filesName: '',
      filesPassword: '',
      filesConfirmPassword: '',
      // null = unknown; false = server has no Zip extension (file backups blocked).
      zipAvailable: null,
      zipEncryption: null,
    };
  }

  componentDidMount() {
    this.load();
    this.loadCapabilities();
  }

  loadCapabilities = () => {
    const client = api();
    if (!client) { return; }
    client.get('connection/backup-capabilities')
      .then((res) => {
        if (errorOf(res)) { return; }
        const d = res.data || {};
        this.setState({ zipAvailable: !!d.zip_available, zipEncryption: !!d.zip_encryption });
      })
      .catch(() => { /* leave as unknown; backend still guards */ });
  };

  load = () => {
    const client = api();
    if (!client) { return; }
    this.setState({ loading: true });
    client.get('connection/backups')
      .then((res) => {
        const err = errorOf(res);
        if (err) { message.error(err, 5); this.setState({ loading: false }); return; }
        const data = res.data;
        this.setState({ backups: Array.isArray(data) ? data : [], loading: false });
      })
      .catch((error) => {
        message.error(httpErrorOf(error) || 'Could not load backups', 5);
        this.setState({ loading: false });
      });
  };

  openModal = () => this.setState({
    modalOpen: true, name: '', password: '', confirmPassword: '',
  });

  closeModal = () => this.setState({ modalOpen: false });

  createBackup = () => {
    const { name, password, confirmPassword } = this.state;
    if (!password || password.length < 6) {
      message.error('Enter a password of at least 6 characters.', 5);
      return;
    }
    if (password !== confirmPassword) {
      message.error('The passwords do not match.', 5);
      return;
    }
    const client = api();
    if (!client) { return; }
    this.setState({ creating: true });
    client.post('connection/backups', { type: 'database', name, password })
      .then((res) => {
        const err = errorOf(res);
        if (err) { message.error(err, 6); this.setState({ creating: false }); return; }
        message.success('Database backup created.');
        this.setState({ creating: false, modalOpen: false });
        this.load();
      })
      .catch((error) => {
        message.error(httpErrorOf(error) || 'Backup failed', 6);
        this.setState({ creating: false });
      });
  };

  openFilesModal = () => this.setState({
    filesModalOpen: true, filesName: '', filesPassword: '', filesConfirmPassword: '',
  });

  closeFilesModal = () => this.setState({ filesModalOpen: false });

  createFilesBackup = () => {
    const { filesName, filesPassword, filesConfirmPassword } = this.state;
    if (filesPassword) {
      if (filesPassword.length < 6) {
        message.error('The password must be at least 6 characters.', 5);
        return;
      }
      if (filesPassword !== filesConfirmPassword) {
        message.error('The passwords do not match.', 5);
        return;
      }
    }
    const client = api();
    if (!client) { return; }
    this.setState({ creatingFiles: true });
    client.post('connection/backups', { type: 'files', name: filesName, password: filesPassword })
      .then((res) => {
        const err = errorOf(res);
        if (err) { message.error(err, 6); this.setState({ creatingFiles: false }); return; }
        message.success('File backup created.');
        this.setState({ creatingFiles: false, filesModalOpen: false });
        this.load();
      })
      .catch((error) => {
        message.error(httpErrorOf(error) || 'File backup failed', 6);
        this.setState({ creatingFiles: false });
      });
  };

  download = (record) => {
    const client = api();
    if (!client) { return; }
    // Stream via the same authenticated proxy the apiClient uses (token in query).
    const url = `${client.clientBaseUrl}api/index.php?token=${encodeURIComponent(client.token)}`
      + `&method=get&url=/connection/backups/${record.id}/download`;
    window.open(url, '_blank', 'noopener');
  };

  remove = (record) => {
    const client = api();
    if (!client) { return; }
    client.delete(`connection/backups/${record.id}`)
      .then((res) => {
        const err = errorOf(res);
        if (err) { message.error(err, 5); return; }
        message.success('Backup deleted.');
        this.load();
      })
      .catch((error) => message.error(httpErrorOf(error) || 'Could not delete backup', 5));
  };

  columns = () => [
    {
      title: 'Name',
      dataIndex: 'name',
      render: (text, record) => (
        <Space>
          {record.type === 'files' ? <FileZipOutlined /> : <DatabaseOutlined />}
          <span>{text || record.file_name}</span>
          {Number(record.encrypted) === 1 && (
            <Tooltip title="Password-encrypted">
              <LockOutlined style={{ color: '#8c8c8c' }} />
            </Tooltip>
          )}
        </Space>
      ),
    },
    {
      title: 'Type',
      dataIndex: 'type',
      width: 110,
      render: (t) => <Tag color={t === 'database' ? 'blue' : 'purple'}>{t}</Tag>,
    },
    {
      title: 'Contents',
      key: 'contents',
      render: (text, record) => {
        const m = parseMeta(record.meta);
        if (record.type === 'files') {
          return (
            <Text type="secondary" style={{ fontSize: 12 }}>
              {(m.file_count != null) ? `${m.file_count} files` : '-'}
            </Text>
          );
        }
        if (record.type === 'database') {
          return (
            <Text type="secondary" style={{ fontSize: 12 }}>
              {(m.table_count != null) ? `${m.table_count} tables` : ''}
              {(m.row_count != null) ? `, ${m.row_count} rows` : ''}
            </Text>
          );
        }
        return <Text type="secondary">-</Text>;
      },
    },
    {
      title: 'Size',
      dataIndex: 'file_size',
      width: 90,
      render: (s) => humanSize(s),
    },
    {
      title: 'Status',
      dataIndex: 'status',
      width: 110,
      render: (s) => <Tag color={s === 'Completed' ? 'green' : (s === 'Failed' ? 'red' : 'orange')}>{s}</Tag>,
    },
    {
      title: 'Created',
      dataIndex: 'created',
      width: 160,
      render: (d) => <Text type="secondary" style={{ fontSize: 12 }}>{d}</Text>,
    },
    {
      title: '',
      key: 'actions',
      width: 170,
      render: (text, record) => (
        <Space>
          <Button size="small" icon={<DownloadOutlined />} onClick={() => this.download(record)}>
            Download
          </Button>
          <Popconfirm
            title="Delete this backup?"
            okText="Delete"
            okButtonProps={{ danger: true }}
            onConfirm={() => this.remove(record)}
          >
            <Button size="small" danger icon={<DeleteOutlined />} />
          </Popconfirm>
        </Space>
      ),
    },
  ];

  render() {
    const {
      backups, loading, modalOpen, creating, name, password, confirmPassword,
    } = this.state;

    return (
      <div style={{ padding: 16 }}>
        <Card
          title={<Space><DatabaseOutlined /> Database Backups</Space>}
          extra={(
            <Space>
              <Button icon={<ReloadOutlined />} onClick={this.load} loading={loading}>Refresh</Button>
              <Tooltip title={this.state.zipAvailable === false ? 'File backups require the PHP Zip extension, which is not installed on this server.' : ''}>
                <Button
                  icon={<FileZipOutlined />}
                  onClick={this.openFilesModal}
                  disabled={this.state.zipAvailable === false}
                >
                  Backup Files
                </Button>
              </Tooltip>
              <Button type="primary" icon={<DatabaseOutlined />} onClick={this.openModal}>Backup Database</Button>
            </Space>
          )}
        >
          <Alert
            type="info"
            showIcon
            style={{ marginBottom: 16 }}
            message="Database and file backups"
            description={(
              <span>
                A <b>database</b> backup exports the whole IceHRM database as JSON, encrypted with a
                password you provide (stored under <Text code>app/data/db_backups</Text>) — keep the
                password safe, it is not recoverable. A <b>file</b> backup zips everything under{' '}
                <Text code>app/data</Text> (excluding the keys, backup folders and the log) into{' '}
                <Text code>app/data/file_backups</Text>.
              </span>
            )}
          />
          <Table
            rowKey="id"
            size="small"
            loading={loading}
            columns={this.columns()}
            dataSource={backups}
            pagination={{ pageSize: 10, hideOnSinglePage: true }}
            locale={{ emptyText: 'No backups yet' }}
          />
        </Card>

        <Modal
          title="Create Database Backup"
          open={modalOpen}
          onCancel={this.closeModal}
          onOk={this.createBackup}
          okText="Create Backup"
          confirmLoading={creating}
        >
          <Form layout="vertical">
            <Form.Item label="Name (optional)">
              <Input
                placeholder="e.g. Before cloud migration"
                value={name}
                onChange={(e) => this.setState({ name: e.target.value })}
              />
            </Form.Item>
            <Form.Item
              label="Encryption password"
              required
              help="At least 6 characters. Required to decrypt the backup later — keep it safe."
            >
              <Input.Password
                prefix={<LockOutlined />}
                value={password}
                onChange={(e) => this.setState({ password: e.target.value })}
              />
            </Form.Item>
            <Form.Item label="Confirm password" required>
              <Input.Password
                prefix={<LockOutlined />}
                value={confirmPassword}
                onChange={(e) => this.setState({ confirmPassword: e.target.value })}
              />
            </Form.Item>
          </Form>
        </Modal>

        <Modal
          title="Create File Backup"
          open={this.state.filesModalOpen}
          onCancel={this.closeFilesModal}
          onOk={this.createFilesBackup}
          okText="Create File Backup"
          confirmLoading={this.state.creatingFiles}
        >
          <Paragraph type="secondary">
            Zips every file under <Text code>app/data</Text> into{' '}
            <Text code>app/data/file_backups</Text>, excluding the{' '}
            <Text code>keys</Text>, <Text code>db_backups</Text> and{' '}
            <Text code>file_backups</Text> folders and <Text code>icehrm.log</Text>.
            File backups are not encrypted.
          </Paragraph>
          <Form layout="vertical">
            <Form.Item label="Name (optional)">
              <Input
                placeholder="e.g. Uploads snapshot"
                value={this.state.filesName}
                onChange={(e) => this.setState({ filesName: e.target.value })}
              />
            </Form.Item>
            <Form.Item
              label="Password (optional)"
              help="Leave blank for a plain zip. If set, the zip is AES-256 encrypted and needs this password to open."
            >
              <Input.Password
                prefix={<LockOutlined />}
                value={this.state.filesPassword}
                onChange={(e) => this.setState({ filesPassword: e.target.value })}
              />
            </Form.Item>
            {this.state.filesPassword ? (
              <Form.Item label="Confirm password" required>
                <Input.Password
                  prefix={<LockOutlined />}
                  value={this.state.filesConfirmPassword}
                  onChange={(e) => this.setState({ filesConfirmPassword: e.target.value })}
                />
              </Form.Item>
            ) : null}
          </Form>
        </Modal>
      </div>
    );
  }
}

export default ConnectionBackups;
