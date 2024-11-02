import React, { useEffect, useState } from 'react';
import {Avatar, Button, List, Skeleton, Space, Typography, Input, Pagination, Divider, Tooltip, Tag} from 'antd';
const { Search } = Input;
const { Paragraph } = Typography;
import { FilterOutlined, UserAddOutlined } from '@ant-design/icons';

class EmployeeList extends React.Component {

  state = {
    initLoading: true,
    loading: false,
    data:[],
    selected:[],
    list:[],
    searchTerm:'',
    total:0,
    count:10,
    employee: null,
  };

  constructor(props) {
    super(props);
    this.setState({employee: props.element});
  }

  setInitLoading = (value) => {
    this.setState({initLoading: value});  
  }

  setLoading = (value) => {
    this.setState({loading: value});
  }

  setData = (value) => {
    this.setState({data: value});  
  }

  setSelected = (value) => {
    this.setState({selected: value});
  }
  
  setList = (value) => {
    this.setState({list: value});  
  }

  setSearchTerm = (value) => {
    this.setState({searchTerm: value});
  }

  setTotal = (value) => {
    this.setState({total: value});
  }

  setCount = (value) => {
    this.setState({count: value});
  }

  reloadList() {
    this.onSearch('');
  }
  
  componentDidMount() {
    this.props.apiClient
      .get(`employees&limit=${this.state.count}&page=1&filters={"status":"Active"}`)
      .then((response) => {
        this.setInitLoading(false);
        this.setData(response.data.data);
        this.setList(response.data.data);
        this.setTotal(response.data.total);
        this.setFirstInTheList(response.data.data);
      });
  }
  onLoadMore = (page, pageSize) => {
    this.setLoading(true);
    this.setList(
      [...new Array(this.state.count)].map(() => ({
          loading: true,
          first_name: {},
          last_name: {},
          image: {},
        }),
      ),
    );

    this.props.apiClient
      .get(`employees&limit=${pageSize}&page=${page}&search=&filters=${this.buildFilterParameters()}`)
      .then((response) => {
        const newData = response.data.data;
        this.setTotal(response.data.total);
        this.setData(newData);
        this.setList(newData);
        this.setLoading(false);
        this.setInitLoading(false);
        this.setFirstInTheList(response.data.data);
        // Resetting window's offsetTop so as to display react-virtualized demo underfloor.
        // In real scene, you can using public method of react-virtualized:
        // https://stackoverflow.com/questions/46700726/how-to-use-public-method-updateposition-of-react-virtualized
        window.dispatchEvent(new Event('resize'));
      });
  };

  setFirstInTheList = (data) => {
    if (data.length > 0) {
      this.props.setEmployee(data[0]);
    }
  };

  onSearch = (value) => {
    this.setInitLoading(true);
    this.setSearchTerm(value);
    if (value == null || value === '') {
      return this.onLoadMore(1 , this.state.count);
    } else {
      this.props.apiClient
        .get(`employees&limit=${this.state.count}&page=1&search=${value}&filters=${this.buildFilterParameters()}`)
        .then((response) => {
          this.setInitLoading(false);
          this.setData(response.data.data);
          this.setList(response.data.data);
          this.setTotal(response.data.total);
          this.setFirstInTheList(response.data.data);
        });
    }
  }

  onChangePagination = (page, pageSize) => {
    this.setCount(pageSize);
    if (this.state.searchTerm == null || this.state.searchTerm === '') {
      this.onLoadMore(page , pageSize);
    } else {
      this.props.apiClient
        .get(`employees&limit=${pageSize}&page=${page}&search=${this.state.searchTerm}&filters=${this.buildFilterParameters()}`)
        .then((response) => {
          this.setInitLoading(false);
          this.setData(response.data.data);
          this.setList(response.data.data);
          this.setTotal(response.data.total);
          this.setFirstInTheList(response.data.data);
        });
    }
    this.props.ice.slowScrollToTop();
  };

  selectItem = (item) => {
    this.setSelected(item);
    this.props.setEmployee(item);
    this.props.ice.reloadEmployee(item.id);
    this.props.ice.slowScrollToTop();
  }

  buildFilterParameters = () => {
    let filters = this.props.ice.filter;
    if (null == filters || filters.length == []) {
      return '{"status":"Active"}';
    } else {
      filters['status'] = 'Active';
      return JSON.stringify(filters);
    }
  }

  renderItem = (item) => {
    if (this.state.selected && this.state.selected.id === item.id) {
      return (
        <List.Item style={{backgroundColor: '#EEE', padding: '10px'}}>
          <Skeleton avatar title={false} loading={item.loading} active>
            <List.Item.Meta
              onClick={() => selectItem(item)}
              avatar={<Avatar src={item.image} />}
              title={item.first_name + ' ' + item.last_name}
              description={`${item.department?.display} | ${item.job_title?.display}`}
            />
          </Skeleton>
        </List.Item>
      );
    }

    return (
      <List.Item style={{padding: '10px'}}>
        <Skeleton avatar title={false} loading={item.loading} active>
          <List.Item.Meta
            onClick={() => this.selectItem(item)}
            avatar={<Avatar src={item.image} />}
            title={item.first_name + ' ' + item.last_name}
            description={`${item.department?.display} | ${item.job_title?.display}`}
          />
        </Skeleton>
      </List.Item>
    );
  }

  render() {
    return (
      <div style={{border: '1px solid rgba(0, 0, 0, 0.05)', padding: '10px'}}>
        <Space direction={'vertical'} style={{width:'100%', textAlign: 'Right'}}>
          <Search placeholder="Search by Name" onSearch={this.onSearch} enterButton />
        </Space>
        <Divider></Divider>
        <List
          className="demo-loadmore-list"
          loading={this.state.initLoading}
          itemLayout="horizontal"
          dataSource={this.state.list}
          renderItem={this.renderItem}
        />
        <Divider></Divider>
        {!this.state.initLoading ? (
          <Pagination
            //size="small"
            total={this.state.total}
            //showTotal={total => `Total ${total} items`}
            defaultPageSize={this.state.count}
            defaultCurrent={1}
            showSizeChanger
            pageSizeOptions={['5', '10', '20', '50', '100']}
            onChange={this.onChangePagination}
          />): null}
      </div>
    );
  }

}
export default EmployeeList;