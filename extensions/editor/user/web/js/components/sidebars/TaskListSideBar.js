import React from 'react';
import {
  Card, Tag, Descriptions, Space, Avatar, Tooltip, Button, message
} from 'antd';
import SaveButton from '../SaveButton';
import { CaretLeftOutlined } from '@ant-design/icons';

import {
    CheckOutlined, CloseOutlined,
} from '@ant-design/icons';

class TaskListSideBar extends React.Component {
  constructor(props) {
    super(props);
  }

  changeTaskListStatus( taskListId, status ) {
    window.editorExtensionController.getApiClient().post(`task-list/${taskListId}/status`, {status})
        .then((response) => {
          if (response.status !== 200) {
            message.error({
              content: 'Error updating task list status',
              key: 'editDocument',
              duration: 2,
              style: {
                marginTop: '7vh',
              },
            });
          }
        }).catch((error) => {
          message.error({
            content: 'Error updating task list status',
            key: 'editDocument',
            duration: 2,
            style: {
              marginTop: '7vh',
            },
          });
    });
  }

  render() {
    const {
      objectType,
      objectId,
      objectField,
      sideBarObject,
    } = this.props;

    const statusToColor = {
      'Draft':'default',
      'Open':'blue',
      'On-hold':'orange',
      'Completed':'green'
    }

    return (
      <>
        <Card
          title={modJs.gt('My To-Do List')}
          style={{
            width: '100%',
          }}
        >
          <Descriptions title="" layout="horizontal" bordered column={1}>
            <Descriptions.Item label="Owner">
              <Space>
                <Avatar src={sideBarObject?.employeeData?.image}/>
                <span>{sideBarObject?.employeeData?.name}</span>
              </Space>
            </Descriptions.Item>
            <Descriptions.Item label="Assignees">
              <Space>
                <Avatar.Group>
                  {sideBarObject?.assigneeData?.map((assignee) =>
                      (<Tooltip title={assignee.name} placement="top">
                        <Avatar src={assignee.image} />
                      </Tooltip>)
                  )}
                </Avatar.Group>
              </Space>
            </Descriptions.Item>
            <Descriptions.Item label="Status"><Tag color={statusToColor[sideBarObject.status]}>{sideBarObject.status}</Tag></Descriptions.Item>
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

export default TaskListSideBar;
