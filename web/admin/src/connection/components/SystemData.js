import React, { useState } from 'react';
import {
  Table,
  Typography,
  Button,
  Modal,
  Alert, Space,Card,
} from 'antd';
import { CopyOutlined, FileTextOutlined } from "@ant-design/icons";
const { Link } = Typography;

function SystemData(props) {
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [isLogFileModalVisible, setIsLogFileModalVisible] = useState(false);
  const { data, issues, logFileRows } = props;
  const columns = [
    {
      title: 'Name',
      dataIndex: 'name',
      key: 'name',
    },
    {
      title: 'Value',
      dataIndex: 'value',
      key: 'value',
    },
  ];

  const showModal = () => {
    setIsModalVisible(true);
  };

  const handleOk = () => {
    setIsModalVisible(false);
  };

  const handleCancel = () => {
    setIsModalVisible(false);
  };

  const showLogFileModal = () => {
    setIsLogFileModalVisible(true);
  };

  const handleLogFileCancel = () => {
    setIsLogFileModalVisible(false);
  };

  const logFileColumns = [
    {
      title: 'Line #',
      dataIndex: 'id',
      key: 'id',
      width: 100,
    },
    {
      title: 'Log Entry',
      dataIndex: 'line',
      key: 'line',
      ellipsis: false,
      render: (text) => (
        <div style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word', fontFamily: 'monospace', fontSize: '12px' }}>
          {text || ''}
        </div>
      ),
    },
  ];

  return (
    <Space direction="vertical" style={{ width: '100%' }}>
      { issues.length > 0 &&
        <Card title="System Issues">
          <Space direction="vertical" style={{width: '100%'}}>
            {issues.map((item) => {
              return (<Space>
                  <Alert
                    message={item.message}
                    type={item.type}
                    showIcon
                  >
                  </Alert>
                  {item.link &&
                  <Button onClick={() => {
                    window.open(item.link, '_blank');
                  }}>
                    {item.linkText}
                  </Button>
                  }

                </Space>
              );
            })}
          </Space>
        </Card>
      }
      <Card title="System Report">
        <Table columns={columns} dataSource={data} />
        <Space>
          <Button type="primary" icon={<CopyOutlined />} onClick={showModal}>
            Copy System Report
          </Button>
          <Button type="default" icon={<FileTextOutlined />} onClick={showLogFileModal}>
            View Last 1000 Log File Rows
          </Button>
        </Space>
      </Card>
      <Modal 
        title="System Data" 
        open={isModalVisible} 
        onOk={handleOk} 
        onCancel={handleCancel}
        width={600}
      >
        <div style={{ maxHeight: '400px', overflowY: 'auto' }}>
          {data.map((item, index) => (
            <p key={index}>{`${item.name}:${item.value}`}</p>
          ))}
        </div>
      </Modal>
      <Modal 
        title="Last 1000 Log File Rows (icehrm.log)" 
        open={isLogFileModalVisible} 
        onCancel={handleLogFileCancel}
        footer={[
          <Button key="close" onClick={handleLogFileCancel}>
            Close
          </Button>
        ]}
        width={1200}
      >
        <div style={{ maxHeight: '600px', overflowY: 'auto' }}>
          <Table 
            columns={logFileColumns} 
            dataSource={logFileRows || []} 
            pagination={false}
            scroll={{ y: 550 }}
            size="small"
            rowKey="id"
          />
        </div>
      </Modal>
    </Space>
  );
}

export default SystemData;
