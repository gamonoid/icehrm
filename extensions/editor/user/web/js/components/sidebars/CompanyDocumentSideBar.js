import React from 'react';
import {
  Card, Descriptions, Space, Button,
} from 'antd';
import { CaretLeftOutlined } from '@ant-design/icons';
import SaveButton from '../SaveButton';
import { editorEnv } from '../../editorEnv';

// Company document content sidebar — shows the document's Name and Description
// (from getEditorSideBarObject) rather than the generic Entity/Identifier/Field
// details CommonSideBar renders.
class CompanyDocumentSideBar extends React.Component {
  render() {
    const { sideBarObject } = this.props;
    const name = sideBarObject && sideBarObject.name ? sideBarObject.name : '';
    const details = sideBarObject && sideBarObject.details ? sideBarObject.details : '';

    return (
      <>
        <Card title={modJs.gt('Document Details')} style={{ width: '100%' }}>
          <Descriptions title="" layout="vertical" bordered column={1}>
            <Descriptions.Item label={modJs.gt('Name')}>{name}</Descriptions.Item>
            <Descriptions.Item label={modJs.gt('Description')}>
              {details || '-'}
            </Descriptions.Item>
          </Descriptions>
        </Card>
        { (!editorEnv.isReadOnly() || editorEnv.canSelectChecks())
        && (
          <Space direction="horizontal" style={{ width: '100%', marginTop: '12px' }} align="right">
            <SaveButton />
            <Button
              type="default"
              icon={<CaretLeftOutlined />}
              onClick={() => editorEnv.close()}
            >
              {modJs.gt('Back')}
            </Button>
          </Space>
        )}
      </>
    );
  }
}

export default CompanyDocumentSideBar;
