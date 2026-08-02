import React from 'react';

import CommonSideBar from './components/sidebars/CommonSideBar';
import CourseSideBar from './components/sidebars/CourseSideBar';
import EmployeeCourseSideBar from './components/sidebars/EmployeeCourseSideBar';
import TaskListSideBar from './components/sidebars/TaskListSideBar';
import CompanyDocumentSideBar from './components/sidebars/CompanyDocumentSideBar';

// object_type -> sidebar component. Add a row to register a new document type's
// sidebar; anything unmapped falls back to CommonSideBar.
const SIDEBAR_REGISTRY = {
  LmsLesson: CourseSideBar,
  LmsCourse: CourseSideBar,
  LmsEmployeeCourse: EmployeeCourseSideBar,
  LmsEmployeeLesson: EmployeeCourseSideBar,
  TaskList: TaskListSideBar,
  CompanyDocument: CompanyDocumentSideBar,
};

class EditorUserExtensionView extends React.Component {
  constructor(props) {
    super(props);
    this.sidebarReference = React.createRef();
  }

  getSideBarComponent(objectType, objectId, objectField, sideBarObject) {
    const SideBar = SIDEBAR_REGISTRY[objectType] || CommonSideBar;
    return (
      <SideBar
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
