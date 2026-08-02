import React from 'react';
import { UserOutlined } from '@ant-design/icons';
import {
  Avatar, Descriptions, Tabs, Skeleton, Typography, Card
} from 'antd';

const { Text, Link, Paragraph, Title } = Typography;

const DirectoryEntry = (props) => {

  if (!props.employee || !props.employee.id) {
    return (<Skeleton active />);
  }

  return (
    <div style={{border: '1px solid rgba(0, 0, 0, 0.05)', padding: '10px'}}>
      <Tabs defaultActiveKey="1">
        <Tabs.TabPane key="1" tab={<span><UserOutlined />{props.ice.gt('General')}</span>}>
          <Card
            bordered={false}
            size="small"
            title={`${props.employee?.first_name ?? ''} ${props.employee?.last_name ?? ''}`}
            style={{
              width: 300,
              margin: '0px 0px 20px 0px',
            }}
          >
            <Avatar src={props.employee?.image} size={64}/>
          </Card>
          <Descriptions title='' layout="vertical" bordered={true}>
            <Descriptions.Item label={<Text underline>{props.ice.gt('Phone')}</Text>}>{<Paragraph copyable>{props.employee?.work_phone ?? ''}</Paragraph>}</Descriptions.Item>
            <Descriptions.Item label={<Text underline>{props.ice.gt('Country')}</Text>}>{<Text strong>{props.employee?.country?.display ?? ''}</Text>}</Descriptions.Item>
            <Descriptions.Item label={<Text underline>{props.ice.gt('Address')}</Text>} span={2}>
              {<Text strong>{props.employee?.full_address ?? ''}</Text>}
            </Descriptions.Item>
            <Descriptions.Item label={<Text underline>{props.ice.gt('Department')}</Text>}>{<Text strong>{props.employee?.department?.display ?? ''}</Text>}</Descriptions.Item>
            <Descriptions.Item label={<Text underline>{props.ice.gt('Designation')}</Text>}>{<Text strong>{props.employee?.job_title?.display ?? ''}</Text>}</Descriptions.Item>

          </Descriptions>
        </Tabs.TabPane>
      </Tabs>

    </div>
  );
}
export default DirectoryEntry;
