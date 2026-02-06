import React from 'react';
import {
  Card, Tag, Descriptions, List, Typography, Space, Progress,
} from 'antd';

import {
  CheckCircleTwoTone, BulbTwoTone, FileTextTwoTone,
} from '@ant-design/icons';
import SaveButton from '../SaveButton';

const {
  Title, Paragraph, Text, Link,
} = Typography;

class CourseSideBar extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const {
      objectType,
      objectId,
      objectField,
      sideBarObject,
    } = this.props;
    return (
      <>
        <Card
          title={<a href={sideBarObject.document_link}>{sideBarObject.name }</a>}

          style={{
            width: '100%',
          }}
        >
          <Space size="large">
            <Space size="large" direction="vertical" align="middle">
              { sideBarObject.short_description !== ''
                    && <p>{sideBarObject.short_description}</p>}
            </Space>
          </Space>
        </Card>
        <Space direction="horizontal" style={{ width: '100%', marginTop: '12px', marginBottom: '3px' }} align="right">
          { (!window.editor_readonly)
          && (
            <SaveButton />
          )}
        </Space>
        <Card
          title="Lessons"
          style={{
            width: '100%',
            marginTop: '10px',
          }}
        >
          <List
            itemLayout="horizontal"
            dataSource={sideBarObject.lessons}
            renderItem={(item) => (
              <List.Item>
                <List.Item.Meta
                  avatar={item.status === 'Draft' ? <FileTextTwoTone twoToneColor="#ffa233" />: <CheckCircleTwoTone twoToneColor="#52c41a" />}
                  title={<a href={item.document_link}>{item.name}</a>}
                />
              </List.Item>
            )}
          />
        </Card>
      </>

    );
  }
}

export default CourseSideBar;
