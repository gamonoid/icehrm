import React, { useState } from 'react';
import {
  Button, Row, Col, Grid,
} from 'antd';
import { ArrowLeftOutlined } from '@ant-design/icons';
import DirectoryList from './components/EmployeeList';
import EmployeeProfile from './components/EmployeeProfile';
import TopBar from './components/TopBar';

const { useBreakpoint } = Grid;

const EmployeeAdminView = (props) => {
  const [employee, setEmployee] = useState([]);
  // True once the directory finished its initial load with zero rows (e.g. a
  // manager who has no subordinates) — lets the profile panel show a message
  // instead of a loading skeleton that would otherwise spin forever.
  const [listEmpty, setListEmpty] = useState(false);
  const setData = (data) => {
    setEmployee(data);
  };

  const screens = useBreakpoint();
  const isMobile = !screens.md; // < 768px
  const hasSelection = !!(employee && employee.id);

  window.modJs.employeeProfileRef = React.createRef();
  window.modJs.employeeListRef = React.createRef();
  window.modJs.employeeTopBar = React.createRef();

  const list = (
    <DirectoryList apiClient={props.apiClient} setEmployee={setData} ice={props.ice} autoSelect={!isMobile} onListLoaded={setListEmpty} ref={window.modJs.employeeListRef} />
  );
  const profile = (
    <EmployeeProfile apiClient={props.apiClient} setEmployee={setData} element={employee} employeeId={employee.id} adapter={props.ice} listEmpty={listEmpty} ref={window.modJs.employeeProfileRef} />
  );

  // On phones, show one panel at a time: the directory until an employee is
  // picked, then the profile (with a Back control). On tablet/desktop keep the
  // side-by-side master/detail layout.
  if (isMobile) {
    return (
      <Row gutter={[0, 12]}>
        <Col span={24}>
          <TopBar ice={props.ice} ref={window.modJs.employeeTopBar} />
        </Col>
        {hasSelection ? (
          <Col span={24}>
            <Button
              type="text"
              icon={<ArrowLeftOutlined />}
              onClick={() => setData([])}
              style={{ marginBottom: 8 }}
            >
              {props.ice && props.ice.gt ? props.ice.gt('Back to list') : 'Back to list'}
            </Button>
            {profile}
          </Col>
        ) : (
          <Col span={24}>{list}</Col>
        )}
      </Row>
    );
  }

  return (
    <Row gutter={16}>
      <Col span={24}>
        <TopBar ice={props.ice} ref={window.modJs.employeeTopBar} />
      </Col>
      <Col xs={24} md={8} lg={6}>
        {list}
      </Col>
      <Col xs={24} md={16} lg={18}>
        {profile}
      </Col>
    </Row>
  );
};
export default EmployeeAdminView;
