import React from 'react';
import {
  Card, Tag, Typography,
} from 'antd';

import CommonSideBar from './components/sidebars/CommonSideBar';
import CourseSideBar from './components/sidebars/CourseSideBar';
import EmployeeCourseSideBar from './components/sidebars/EmployeeCourseSideBar';
import TaskListSideBar from "./components/sidebars/TaskListSideBar";

const { Text } = Typography;


class EditorUserExtensionView extends React.Component {
  constructor(props) {
    super(props);
    this.sidebarReference = React.createRef();
  }

  getSideBarComponent(objectType, objectId, objectField, sideBarObject) {
    if (objectType === 'LmsLesson' || objectType === 'LmsCourse') {
      return (
        <CourseSideBar
          ref={this.sidebarReference}
          objectType={objectType}
          objectId={objectId}
          objectField={objectField}
          sideBarObject={sideBarObject}
        />
      );
    }

    if (objectType === 'LmsEmployeeCourse' || objectType === 'LmsEmployeeLesson') {
      return (
        <EmployeeCourseSideBar
          ref={this.sidebarReference}
          objectType={objectType}
          objectId={objectId}
          objectField={objectField}
          sideBarObject={sideBarObject}
        />
      );
    }

    if (objectType === 'TaskList') {
      return (
          <TaskListSideBar
              ref={this.sidebarReference}
              objectType={objectType}
              objectId={objectId}
              objectField={objectField}
              sideBarObject={sideBarObject}
          />
      );
    }


    return (
      <CommonSideBar
        ref={this.sidebarReference}
        objectType={objectType}
        objectId={objectId}
        objectField={objectField}
        sideBarObject={sideBarObject}
      />
    );
  }

  render() {
    const objectType = window.object_type;
    const objectId = window.object_id;
    const objectField = window.object_field;
    const { sideBarObject } = window;
    return (
      <>
        {this.getSideBarComponent(objectType, objectId, objectField, sideBarObject)}
      </>
    );
  }
}

export default EditorUserExtensionView;
