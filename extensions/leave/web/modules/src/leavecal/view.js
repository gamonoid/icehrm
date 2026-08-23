import React from 'react';

import {
  Badge, Calendar, Avatar, Space, Tooltip, Tag, Divider, Alert
} from 'antd';

class LeaveCalendarView extends React.Component {

  state = {
    loading: true,
    data:[],
    yearData:[],
    holidays:[],
    year:new Date().getFullYear(),
    month:(new Date().getMonth()) + 1,
  };

  constructor(props) {
    super(props);
  }

  setLoading = (value) => {
    this.setState({loading: value});
  }

  mergeData = (data) => {
    let prev = this.state.data;
    for (let index in data) {
      prev[index] = data[index];
    }
    this.setState({data: prev});
  }

  mergeYearlyData = (data) => {
    let prev = this.state.yearData;
    for (let index in data) {
      prev[index] = data[index];
    }
    console.log(prev);
    this.setState({yearData: prev});
  }

  componentDidMount() {
    this.loadMonthlyEvents();
  }

  normalizeMonthDate = (month) => {
    month = parseInt(month);
    if (month < 10) {
      return `0${month}`;
    }
    return month;
  }

  loadMonthlyEvents = () => {
    console.log(`leave-calendar/month/${this.state.year}/${this.normalizeMonthDate(this.state.month)}`);
    this.props.apiClient
      .get(`leave-calendar/month/${this.state.year}/${this.normalizeMonthDate(this.state.month)}`)
      .then((response) => {
        console.log(response);
        this.setLoading(false);
        this.mergeData(response.data.leave);
        this.setState({holidays: response.data.holidays});
      });
  }

  loadYearlyEvents = () => {
    console.log(`leave-calendar/year/${this.state.year}`);
    this.props.apiClient
      .get(`leave-calendar/year/${this.state.year}`)
      .then((response) => {
        console.log(response);
        this.setLoading(false);
        this.mergeYearlyData(response.data);
      });
  }

  getListData = (value) => {
    const date = `${value.year()}-${this.normalizeMonthDate(value.month()+1)}-${this.normalizeMonthDate(value.date())}`;
    return this.state.data[date] || [];
  }

  getMonthData = (value) => {
    console.log('year:'+value.year());
    console.log('month:'+value.month());
    const month = `${value.year()}-${this.normalizeMonthDate(value.month()+1)}`;
    if (!this.state.yearData[value.year()]) {
      return [];
    }
    return this.state.yearData[value.year()][month] || [];
  }

  onPanelChange = (value, mode) => {
    console.log(`${value.date()} ${value.month()} ${value.year()} ${mode}`);
    if (mode === 'month') {
      this.setState(
        {year: value.year(), month: this.normalizeMonthDate(value.month() + 1)},
        () => {
          this.loadMonthlyEvents()
        }
      );
    } else {
      this.setState(
        {year: value.year()},
        () => {
          this.loadYearlyEvents()
        }
      );
    }
  }

  render() {
    const monthCellRender = (value) => {
      const yearData = this.getMonthData(value);
      return yearData ? (
        <div style={{marginTop:'10px', marginRight:'10px'}}>
            {yearData.map((item) => (
              <Tooltip placement="top" title={item.employee.name} color={'#108ee9'} key={item.employee.id}>
                <Badge color="green" size="small" count={item.count}><Avatar src={item.employee.image} style={{marginLeft:'10px', marginBottom:'10px'}}/></Badge>
              </Tooltip>
            ))}
        </div>
      ) : null;
    };
    const dateCellRender = (value) => {
      const listData = this.getListData(value);
      const holiday = this.state.holidays?.find((element) => element?.dateh === `${value.year()}-${this.normalizeMonthDate(value.month()+1)}-${this.normalizeMonthDate(value.date())}`);
      return (
        <div style={{marginTop:'5px', marginRight:'2px'}}>
          {holiday && <><Alert message={holiday.name} type="warning"/><br/><br/></>}

          {listData.map((item) => (
            <Tooltip placement="top" title={item.employee.name} color={'#108ee9'} key={item.employee.id}>
              <Badge color={item.status === 'Approved'? "green" : "orange"} dot={true}><Avatar src={item.employee.image} style={{marginLeft:'10px', marginBottom:'10px'}}/></Badge>
            </Tooltip>
          ))}
        </div>
      );
    };
    return <Calendar dateCellRender={dateCellRender} monthCellRender={monthCellRender} onPanelChange={this.onPanelChange}/>;
  }
}
export default LeaveCalendarView;
