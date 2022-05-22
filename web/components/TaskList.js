import React from 'react';
import {
  Timeline, Drawer, Empty, Button, Space, Typography, Popover,
} from 'antd';
import {
  FireOutlined,
  AlertTwoTone,
  InfoCircleOutlined,
  PauseCircleOutlined,
  MedicineBoxOutlined,
  WarningTwoTone,
} from '@ant-design/icons';

const { Paragraph } = Typography;

class TaskList extends React.Component {
  state = {
    tasks: [],
    showAll: false,
  }

  constructor(props) {
    super(props);
    this.state.tasks = this.props.tasks.map(item => false);
  }

  render() {
    return this.createTaskList(4);
  }

  createTaskList(maxNumberOfTasks) {
    const tasks = this.props.tasks.slice(0, maxNumberOfTasks);
    return (
      <>
        <Space direction="vertical" size="large" style={{ width: '100%' }}>
          {this.props.tasks && this.props.tasks.length > 0
          && (
            <Space direction="vertical" style={{ width: '100%' }}>
              <Timeline style={{ width: '100%' }}>
                {tasks.map(
                  (task, index) => (
                    this.createTask(task, index)
                  ),
                )}
              </Timeline>
              {this.props.tasks.length > maxNumberOfTasks &&
              <Button type="primary" onClick={() => this.showAllTasks()}>
                View All
                {' '}
                {this.props.tasks.length}
                {' '}
                Tasks
              </Button>
              }
            </Space>
          )}
          {this.props.tasks && this.props.tasks.length === 0
          && <Empty description="You're all caught up!" />}
        </Space>
        <Drawer
          title="Task List"
          width={470}
          onClose={() => this.hideAllTasks()}
          visible={this.state.showAll}
          bodyStyle={{ paddingBottom: 80 }}
          zIndex={1200}
          maskClosable={false}
        >
          <Timeline style={{ width: '100%' }}>
            {this.props.tasks.map(
              (task, index) => (
                this.createTask(task, index)
              ),
            )}
          </Timeline>
        </Drawer>
      </>
    );
  }

  visitLink(link) {
    setTimeout(() => {window.open(link);}, 100);
  }

  handleTaskHover(index) {
    this.setState({tasks : this.props.tasks.map((item,i) => index === i)})
  }

  createTask(task, index) {
    if (task.priority === 1000) {
      return (
        <Timeline.Item key={index} onMouseEnter={() => this.handleTaskHover(index)}
                       dot={<AlertTwoTone style={{ fontSize: '16px' }} twoToneColor="red" />} >
          {this.getText(task)}
          {task.link && this.state.tasks[index]
          && (
            <Button type="link" onClick={() => this.visitLink(task.link)}>
              <MedicineBoxOutlined style={{ fontSize: '16px' }} />
              {' '}
              {task.action}
            </Button>
          )}
        </Timeline.Item>
      );
    } else if (task.priority === 100) {
      return (
        <Timeline.Item key={index} onMouseEnter={() => this.handleTaskHover(index)}
          dot={<FireOutlined style={{ fontSize: '16px' }} />} color="red" >
          {this.getText(task)}
          {task.link && this.state.tasks[index]
          && (
          <Button type="link" onClick={() => this.visitLink(task.link)}>
            <MedicineBoxOutlined style={{ fontSize: '16px' }} />
            {' '}
            {task.action}
          </Button>
          )}
        </Timeline.Item>
      );
    } else if (task.priority === 50) {
      return (
        <Timeline.Item key={index} onMouseEnter={() => this.handleTaskHover(index)}
          dot={<WarningTwoTone style={{ fontSize: '16px' }} twoToneColor="#f57b42" />}>
          {this.getText(task)}
          {task.link && this.state.tasks[index]
          && (
          <Button type="link" onClick={() => this.visitLink(task.link)}>
            <MedicineBoxOutlined style={{ fontSize: '16px' }} />
            {' '}
            {task.action}
          </Button>
          )}
        </Timeline.Item>
      );
    } else if (task.priority === 20) {
      return (
        <Timeline.Item key={index} onMouseEnter={() => this.handleTaskHover(index)}
          dot={<InfoCircleOutlined style={{ fontSize: '16px' }} />} color="blue">
          {this.getText(task)}
          {task.link && this.state.tasks[index]
          && (
          <Button type="link" onClick={() => this.visitLink(task.link)}>
            <MedicineBoxOutlined style={{ fontSize: '16px' }} />
            {' '}
            {task.action}
          </Button>
          )}
        </Timeline.Item>
      );
    } else if (task.priority === 10) {
      return (
        <Timeline.Item key={index} onMouseEnter={() => this.handleTaskHover(index)}
          dot={<PauseCircleOutlined style={{ fontSize: '16px' }} />} color="green">
          {this.getText(task)}
          {task.link && this.state.tasks[index]
          && (
          <Button type="link" onClick={() => this.visitLink(task.link)}>
            <MedicineBoxOutlined style={{ fontSize: '16px' }} />
            {' '}
            {task.action}
          </Button>
          )}
        </Timeline.Item>
      );
    }
  }

  getText(task) {
    if (!task.details) {
      return (<Paragraph
        ellipsis={{
          rows: 1,
          expandable: true,
        }}
      >
        {task.text}
      </Paragraph>);
    }
    return (
      <Popover content={task.details}>
        <Paragraph
          ellipsis={{
            rows: 1,
            expandable: true,
          }}
        >
          {task.text}
        </Paragraph>
      </Popover>
    );
  }

  showAllTasks() {
    this.setState({showAll: true});
  }

  hideAllTasks() {
    this.setState({showAll: false});
  }

}

export default TaskList;
