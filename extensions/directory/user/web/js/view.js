import React, { useEffect, useState } from 'react';
import {
  Avatar, Button, List, Skeleton, Space, Typography, Input, Pagination, Divider, Row, Col,
} from 'antd';
import { PhoneTwoTone, MailTwoTone } from '@ant-design/icons';
import DirectoryList from './components/DirectoryList';
import DirectoryEntry from './components/DirectoryEntry';

const { Search } = Input;
const { Paragraph } = Typography;

const DirectoryUserExtensionView = (props) => {
  const [employee, setEmployee] = useState([]);
  const setData = (data) => {
    setEmployee(data);
  };

  return (
    <Row gutter={16}>
      <Col span={8}>
        <DirectoryList apiClient={props.apiClient} setEmployee={setData} ice={props.ice} />
      </Col>
      <Col span={16}>
        <DirectoryEntry apiClient={props.apiClient} employee={employee} ice={props.ice} />
      </Col>
    </Row>
  );
};
export default DirectoryUserExtensionView;
