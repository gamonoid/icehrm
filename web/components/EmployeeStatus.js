import React from 'react';
import {Button, Dropdown, Menu, Space, Skeleton, Input, Typography, Tag} from 'antd';
const { TextArea } = Input;
import {
  WonderfulFaceIcon,
  HappyFaceIcon,
  NeutralFaceIcon,
  SadFaceIcon,
  SleepyFaceIcon,
  ExhaustedFaceIcon,
  MotivatedIcon,
  DoNotDisturbIcon,
  BusyIcon,
  OutForLunchIcon,
  AwayIcon,
  MeetingIcon,
  AvailableIcon,
  NotSetIcon,
  SickIcon,
} from './CustomIcons';

const { Title, Text } = Typography;

import {DownOutlined, EditOutlined} from "@ant-design/icons";


class EmployeeStatus extends React.Component {
  state = {
    feeling: 0,
    status: 0,
    message: '',
    loading: true,
  }

  constructor(props) {
    super(props);
    this.inputRef = React.createRef();
    this.openModelCallback = props.openModelCallback;
  }

  componentDidMount() {
    this.fetch();
  }

  fetch() {
    this.props.apiClient
      .get(`employees/${this.props.employee}/status`)
      .then((response) => {
        const feeling = response.data.status != null ? response.data.feeling : 0;
        const status = response.data.status != null ? response.data.status : 0;
        this.setState({
          feeling: parseInt(feeling, 10),
          status: parseInt(status, 10),
          message: response.data.message,
          loading: false,
        });
      });
  }

  handleStatusSelect(e) {
    this.setState({
      status: parseInt(e.key, 10),
      loading: true,
    }, () => {this.syncState()});
  }

  handleFeelingSelect(e) {
    this.setState({
      feeling: parseInt(e.key, 10),
      loading: true,
    }, () => {this.syncState()});
  }

  onTextClick() {
    this.openModelCallback();
  }

  onMessageChange() {
    this.setState({
      message: this.inputRef.resizableTextArea.textArea.value ?? '',
    });
  }

  onMessageBlur() {
    this.setState({
      message: this.inputRef.resizableTextArea.textArea.value ?? '',
    }, () => {this.syncState()});
  }

  syncState() {
    const data = {
      feeling: this.state.feeling,
      status: this.state.status,
      message: this.state.message,
    }
    this.props.apiClient
      .post(`employees/${this.props.employee}/status`, data)
      .then((response) => {
        this.setState({
          feeling: parseInt(response.data.feeling, 10),
          status: parseInt(response.data.status, 10),
          message: response.data.message,
          loading: false,
        });
      });
  }

  render() {

    const getStatusData = () => {
      const states = {};
      states[0] = ['No Status Set', <NotSetIcon />];
      states[1] = ['Feeling Wonderful', <WonderfulFaceIcon />];
      states[2] = ['Feeling Happy', <HappyFaceIcon />];
      states[3] = ['Feeling OK', <NeutralFaceIcon />];
      states[4] = ['Feeling Sad', <SadFaceIcon />];
      states[5] = ['Feeling Sleepy', <SleepyFaceIcon />];
      states[6] = ['Feeling Exhausted', <ExhaustedFaceIcon />];
      states[7] = ['Feeling Motivated', <MotivatedIcon />];
      states[8] = ['Do not Disturb', <DoNotDisturbIcon />];
      states[9] = ['Busy', <BusyIcon />];
      states[10] = ['Out for Lunch', <OutForLunchIcon />];
      states[11] = ['Away', <AwayIcon />];
      states[12] = ['Available', <AvailableIcon />];
      states[13] = ['In a Meeting', <MeetingIcon />];
      states[14] = ['Not Feeling Well', <SickIcon />];

      return states;
    }

    const menuFeelings = (
      <Menu onClick={this.handleFeelingSelect.bind(this)}>
        <Menu.Item key="1" icon={<WonderfulFaceIcon />}>
          Feeling Wonderful
        </Menu.Item>
        <Menu.Item key="2" icon={<HappyFaceIcon />}>
          Feeling Happy
        </Menu.Item>
        <Menu.Item key="3" icon={<NeutralFaceIcon />}>
          Feeling OK
        </Menu.Item>
        <Menu.Item key="4" icon={<SadFaceIcon />}>
          Feeling Sad
        </Menu.Item>
        <Menu.Item key="5" icon={<SleepyFaceIcon />}>
          Feeling Sleepy
        </Menu.Item>
        <Menu.Item key="6" icon={<ExhaustedFaceIcon />}>
          Feeling Exhausted
        </Menu.Item>
        <Menu.Item key="7" icon={<MotivatedIcon />}>
          Feeling Motivated
        </Menu.Item>
        <Menu.Item key="14" icon={<SickIcon />}>
          Not Feeling Well
        </Menu.Item>
      </Menu>
    );

    const menuStatus = (
      <Menu onClick={this.handleStatusSelect.bind(this)}>
        <Menu.Item key="8" icon={<DoNotDisturbIcon />}>
          Do not Disturb
        </Menu.Item>
        <Menu.Item key="9" icon={<BusyIcon />}>
          Busy
        </Menu.Item>
        <Menu.Item key="10" icon={<OutForLunchIcon />}>
          Out for Lunch
        </Menu.Item>
        <Menu.Item key="11" icon={<AwayIcon />}>
          Away
        </Menu.Item>
        <Menu.Item key="12" icon={<AvailableIcon />}>
          Available
        </Menu.Item>
        <Menu.Item key="13" icon={<MeetingIcon />}>
          In a Meeting
        </Menu.Item>
      </Menu>
    );

    const getIcon = (id) => {

      const data = getStatusData();
      const item = data[id];
      const icon = (item != null) ? item[1] : null;

      if (icon == null) {
        return (<AwayIcon />)
      }

      return icon;
    }

    const getStatusDescription = (id) => {

      const data = getStatusData();
      const item = data[id];
      const label = (item != null) ? item[0] : null;
      if (label == null) {
        return 'Away';
      }
      return label;
    }

    return (
      <Space direction={'vertical'} style={{width: '100%'}}>
        {this.props.showStatusSelect &&
          <Space style={{width: '100%'}}>
            <Dropdown overlay={menuFeelings}>
              <Button>
                {getStatusDescription(this.state.feeling)} {getIcon(this.state.feeling)} <DownOutlined/>
              </Button>
            </Dropdown>

            <Dropdown overlay={menuStatus}>
              <Button>
                {getStatusDescription(this.state.status)} {getIcon(this.state.status)} <DownOutlined/>
              </Button>
            </Dropdown>
          </Space>
        }
        {!this.props.showInput &&
        <Space style={{width: '100%'}}>
          <Space direction={'vertical'} style={{width: '100%', marginTop:'5px'}}>
            <Title level={5}>{this.props.adapter.gt('Daily Plan')}</Title>
              { this.state.message &&
                    <Text onClick={this.onTextClick.bind(this)}>{this.props.adapter.gt("Adjust your daily goal")}</Text>
              }
              { !this.state.message &&
                  <Text onClick={this.onTextClick.bind(this)}>{this.props.adapter.gt("What’s your top priority for today?")}</Text>
              }
            <Tag  bordered={false} color="processing" onClick={this.onTextClick.bind(this)}>
              {this.props.adapter.gt('Update')}
            </Tag>
          </Space>
        </Space>
        }
        <>
        {this.props.showInput && this.state.message &&
            <TextArea
              ref={(ref) => { this.inputRef = ref;}}
              onBlur={ this.onMessageBlur.bind(this) }
              maxLength={300}
              defaultValue={this.state.message}
              placeholder={this.props.adapter.gt("Set your goal for the day!")}
              rows={3}
              autoSize={{ minRows: 3, maxRows: 3 }}
              allowClear
              style={{width: '100%'}}/>
            }
            {this.props.showInput && !this.state.message &&
            <TextArea
              ref={(ref) => { this.inputRef = ref;}}
              onBlur={ this.onMessageBlur.bind(this) }
              maxLength={300}
              placeholder={this.props.adapter.gt("Set your goal for the day!")}
              rows={3}
              autoSize={{ minRows: 3, maxRows: 3 }}
              allowClear
              style={{width: '100%'}}/>
          }
          </>
      </Space>

    );
  }
}

export default EmployeeStatus;
