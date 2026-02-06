import React from 'react';
import {
  Card, Tag, Descriptions, Space, Button,
} from 'antd';
import {
  CaretLeftOutlined,
} from '@ant-design/icons';
import SaveButton from '../SaveButton';

class CommonSideBar extends React.Component {
  constructor(props) {
    super(props);
  }

  // save() {
  //   console.log('CommonSideBar Save');
  // }

  render() {
    const {
      objectType,
      objectId,
      objectField,
    } = this.props;
    return (
      <>
        <Card
          title={modJs.gt('Document Details')}
          style={{
            width: '100%',
          }}
        >
          <Descriptions title="" layout="horizontal" bordered column={1}>
            <Descriptions.Item label="Entity"><Tag color="green">{objectType}</Tag></Descriptions.Item>
            <Descriptions.Item label="Identifier"><Tag color="green">{objectId}</Tag></Descriptions.Item>
            <Descriptions.Item label="Field"><Tag color="green">{objectField}</Tag></Descriptions.Item>
          </Descriptions>
        </Card>
        { (!window.editor_readonly || window.editor_can_select_checks)
        && (
          <Space direction="horizontal" style={{ width: '100%', marginTop: '12px' }} align="right">
            <SaveButton />
            <Button
                type="default"
                icon={<CaretLeftOutlined />}
                onClick={() => history.back()}
            >
              {modJs.gt('Back')}
            </Button>
          </Space>
        )}
      </>
    );
  }
}

export default CommonSideBar;
