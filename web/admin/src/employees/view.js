import React, { useEffect, useState } from 'react';
import {
  Avatar, Button, List, Skeleton, Space, Typography, Input, Pagination, Divider, Row, Col,
} from 'antd';
import { PhoneTwoTone, MailTwoTone } from '@ant-design/icons';
import DirectoryList from './components/EmployeeList';
import EmployeeProfile from './components/EmployeeProfile';
import TopBar from './components/TopBar';

const { Search } = Input;
const { Paragraph } = Typography;

const EmployeeAdminView = (props) => {
  const [employee, setEmployee] = useState([]);
  const setData = (data) => {
    setEmployee(data);
  };

  window.modJs.employeeProfileRef = React.createRef();
  window.modJs.employeeListRef = React.createRef();
  window.modJs.employeeTopBar = React.createRef();

  return (
    <Row gutter={16}>
      <Col span={24}>
        <TopBar ice={props.ice} ref={window.modJs.employeeTopBar} />
      </Col>
      <Col span={6}>
        <DirectoryList apiClient={props.apiClient} setEmployee={setData} ice={props.ice} ref={window.modJs.employeeListRef} />
      </Col>
      <Col span={18}>
        <EmployeeProfile apiClient={props.apiClient} setEmployee={setData} element={employee} employeeId={employee.id} adapter={props.ice} ref={window.modJs.employeeProfileRef} />
      </Col>
    </Row>
  );
};
export default EmployeeAdminView;
