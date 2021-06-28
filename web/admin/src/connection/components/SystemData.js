import React, { useState } from 'react';
import {
  Table,
  Typography,
  Button,
  Modal,
  Alert, Space,Card,
} from 'antd';
import { CopyOutlined } from "@ant-design/icons";
const { Link } = Typography;

function SystemData(props) {
  const [isModalVisible, setIsModalVisible] = useState(false);
  const { data, issues } = props;
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
        <Button type="primary" icon={<CopyOutlined />} onClick={showModal}>
          Copy System Report
        </Button>
      </Card>
      <Modal title="System Data" visible={isModalVisible} onOk={handleOk} onCancel={handleCancel}>
        {data.map((item) => (<p>{`${item.name}:${item.value}`}</p>))}
      </Modal>
    </Space>
  );
}

export default SystemData;
