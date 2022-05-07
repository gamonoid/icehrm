import React, {Component} from 'react';
import {Col, Form, Input, Row, Table, Space, Button, Tag, message} from 'antd';
import {
  FilterOutlined,
  PlusCircleOutlined,
} from '@ant-design/icons';
const { Search } = Input;

class IceTable extends React.Component {
  state = {
    data: [],
    pagination: {},
    loading: true,
    fetchConfig: false,
    //filter: null,
    showLoading: true,
    currentElement: null,
    fetchCompleted: false,
  };

  constructor(props) {
    super(props);
  }

  componentDidMount() {
    const fetchConfig = {
      page: 1,
    };
    message.config({
      top: 40,
    });
    this.setState({
      fetchConfig,
      //filter: this.props.adapter.filter,
      pagination: { 'pageSize': this.props.reader.pageSize }
    });
    //this.fetch(fetchConfig);
  }

  handleTableChange = (pagination, filters, sorter) => {
    const pager = { ...this.state.pagination };
    const { search } = this.state;
    pager.current = pagination.current;
    this.setState({
      pagination: pager,
    });

    const fetchConfig = {
      limit: pagination.pageSize,
      page: pagination.current,
      sortField: sorter.field,
      sortOrder: sorter.order,
      filters: JSON.stringify(filters),
      search: search,
    };

    this.setState({
      fetchConfig
    });

    this.fetch(fetchConfig);
  };

  reload = () => {
    const fetchConfig = this.state.fetchConfig;
    if (fetchConfig) {
      this.fetch(fetchConfig)
    }
  };

  search = (value) => {
    const pager = { ...this.state.pagination };
    this.setState({ search: value, refreshSearch: true });
    const fetchConfig = this.state.fetchConfig;
    console.log(fetchConfig);
    if (fetchConfig) {
      fetchConfig.page = 1;
      pager.current = 1;
      fetchConfig.search = value;
      this.setState({
        fetchConfig,
        pagination: pager,
      }, () => this.fetch(fetchConfig));
    }
  }

  addNew = () => {
    this.props.adapter.renderForm();
  }

  showFilters = () => {
    this.props.adapter.showFilters();
  }

  setFilterData = (filter) => {
    this.setState({
      filter,
    });
  }

  setCurrentElement = (currentElement) => {
    this.setState({currentElement});
  }

  setLoading(value) {
    this.setState({ loading: value });
  }

  fetch = (params = {}) => {
    //this.setState({ loading: this.state.showLoading });
    this.setState({ loading: true });
    //const hideMessage = message.loading({ content: 'Loading Latest Data ...', key: 'loadingTable', duration: 1});
    const pagination = { ...this.state.pagination };

    if (this.props.adapter.localStorageEnabled) {
      try {
        const cachedResponse = this.props.reader.getCachedResponse(params);
        if (cachedResponse.items) {
          this.setState({
            loading: false,
            data: cachedResponse.items,
            pagination,
            showLoading: false,
          });
        } else {
          this.props.reader.clearCachedResponse(params);
        }
      } catch (e) {
        this.props.reader.clearCachedResponse(params);
      }
    }

    this.props.reader.get(params)
      .then(data => {
        // Read total count from server
        // pagination.total = data.totalCount;
        pagination.total = data.total;
        //hideMessage();
        // setTimeout(
        //   () => message.success({ content: 'Loading Completed!', key: 'loadingSuccess', duration: 1 }),
        //   600
        // );
        this.setState({
          loading: false,
          data: data.items,
          pagination,
          showLoading: false,
          fetchCompleted: true,
        });
      });
  };

  getChildrenWithProps(element) {
    const childrenWithProps = React.Children.map(this.props.children, child => {
      // checking isValidElement is the safe way and avoids a typescript error too
      const props = {
        element,
        adapter: this.props.adapter,
        loading: this.state.loading,
      };
      if (React.isValidElement(child)) {
        return React.cloneElement(child, props);
      }
      return child;
    });

    return childrenWithProps;
  }

  render() {
    return (
      <Row direction="vertical" style={{ width: '100%' }}>
        {(!this.state.currentElement || this.props.adapter.keepTableVisibleWhileShowingCustomView()) &&
        <Col span={24}>
          <Row gutter={24}>
            <Col span={18}>
              <Space>
                {this.props.adapter.hasAccess('save') && this.props.adapter.getShowAddNew() &&
                <Button type="primary" onClick={this.addNew}><PlusCircleOutlined/> Add New</Button>
                }
                {this.props.adapter.hasCustomTopButtons() &&
                this.props.adapter.getCustomTopButtons()
                }
                {this.props.adapter.getFilters() &&
                <Button onClick={this.showFilters}><FilterOutlined/> Filters</Button>
                }

                {this.state.fetchCompleted
                && this.props.adapter.getFilters()
                && this.props.adapter.filter != null
                && this.props.adapter.filter !== []
                && this.props.adapter.filter !== ''
                && this.props.adapter.getFilterString(this.props.adapter.filter) !== '' &&
                <Tag closable
                     style={{'lineHeight': '30px'}}
                     color="blue"
                     onClose={() => this.props.adapter.resetFilters()}
                     visible={this.props.adapter.filter != null && this.props.adapter.filter !== [] && this.props.adapter.filter !== ''}
                >
                  {this.props.adapter.getFilterString(this.props.adapter.filter)}
                </Tag>
                }

              </Space>
            </Col>
            <Col span={6}>
              <Form
                ref={(formRef) => this.form = formRef}
                name="advanced_search"
                className="ant-advanced-search-form"
              >
                <Form.Item name="searchTerm" label=""
                           rules={[
                             {
                               required: false,
                             },
                           ]}
                >
                  <Search
                    placeholder="input search text"
                    enterButton="Search"
                    onSearch={value => this.search(value)}
                  />
                </Form.Item>
              </Form>
            </Col>
          </Row>
          <Row gutter={24}>
            <Col span={24}>
              <Table
                // bordered
                rowClassName={(record, index) => index % 2 === 0 ? 'table-row-light' : 'table-row-dark'}
                columns={this.props.columns}
                rowKey={record => record.id}
                dataSource={this.state.data}
                pagination={this.state.pagination}
                loading={this.state.loading}
                onChange={this.handleTableChange}
                reader={this.props.dataPipe}
              />
            </Col>
          </Row>
        </Col>
        }
        {this.state.currentElement &&
          this.getChildrenWithProps(this.state.currentElement)
        }
      </Row>
    );
  }
}

export default IceTable;
