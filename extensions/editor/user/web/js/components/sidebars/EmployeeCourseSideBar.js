import React from 'react';
import {
  Card, Tag, Descriptions, List, Typography, Space, Progress, Button, message,
} from 'antd';

import {
  CheckCircleTwoTone, BulbTwoTone, FileTextTwoTone,
} from '@ant-design/icons';
import SaveButton from '../SaveButton';

const {
  Title, Paragraph, Text, Link,
} = Typography;

class EmployeeCourseSideBar extends React.Component {
  constructor(props) {
    super(props);
  }

  completeLesson(lessonId) {
    window.editorExtensionController.getApiClient().get(`learn/lesson/${lessonId}/check-answers`)
      .then((response) => {
        if (response.status !== 200) {
          message.error({
            content: 'Error completing the lesson, please contact us via team@icehrm.com',
            key: 'editDocument',
            duration: 2,
            style: {
              marginTop: '7vh',
            },
          });
          return;
        }
        if (!response.data.result) {
          message.error({
            content: `Answer to the question "${response.data.question}" is incorrect. Please check and try again.`,
            key: 'editDocument',
            duration: 3,
            style: {
              marginTop: '7vh',
            },
          });
        } else {
          window.location = this.getLessonById(lessonId).document_link_next;
        }
      }).catch((error) => {
        message.error({
          content: 'Error completing the lesson, please contact us via team@icehrm.com',
          key: 'editDocument',
          duration: 2,
          style: {
            marginTop: '7vh',
          },
        });
      });
  }

  getLessonById(id) {
      const { sideBarObject } = this.props;
      return sideBarObject.lessons.find((element) => element.id === id);
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
              <Progress type="circle" percent={sideBarObject.progress} width={80} />
            </Space>
            <Space size="large" direction="vertical" align="middle">
              { sideBarObject.short_description !== ''
                                && <p>{sideBarObject.short_description}</p>}
            </Space>
          </Space>
        </Card>
        { (sideBarObject.currentLesson)
          && (
          <Card
            title={`Lesson: ${sideBarObject.currentLesson.name}`}

            style={{
              width: '100%',
            }}
          >
            <Space size="large">
              <Space size="large" direction="vertical" align="middle">
                { sideBarObject.currentLesson.short_description !== ''
                          && <p>{sideBarObject.currentLesson.short_description}</p>}
              </Space>
            </Space>
          </Card>
          )}
        <Space direction="horizontal" style={{ width: '100%', marginTop: '12px', marginBottom: '3px' }} align="right">
          { (sideBarObject.currentLesson && sideBarObject.currentLesson.status === 'Pending')
                        && (
                        <Button
                          icon={<CheckCircleTwoTone />}
                          onClick={() => this.completeLesson(sideBarObject.currentLesson.id)}
                        >
                          {modJs.gt('Mark Lesson as Completed')}
                        </Button>
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
                {sideBarObject.currentLesson && item.id === sideBarObject.currentLesson.id
                    && (
                    <List.Item.Meta
                      avatar={item.status === 'Pending' ? <FileTextTwoTone twoToneColor="#ffa233" /> : <CheckCircleTwoTone twoToneColor="#52c41a" />}
                      title={<a href={item.document_link}>{item.name}</a>}
                      description={item.short_description}
                    />
                    )}
                {(!sideBarObject.currentLesson || item.id !== sideBarObject.currentLesson.id)
                      && (
                      <List.Item.Meta
                        avatar={item.status === 'Pending' ? <FileTextTwoTone twoToneColor="#ffa233" /> : <CheckCircleTwoTone twoToneColor="#52c41a" />}
                        title={<a href={item.document_link}>{item.name}</a>}
                      />
                      )}
              </List.Item>
            )}
          />
        </Card>
      </>

    );
  }
}

export default EmployeeCourseSideBar;
