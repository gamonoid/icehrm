import React from 'react';
import {Select, Switch, ConfigProvider} from 'antd';
import './css/user-view-switch.css'

class UserViewSwitch extends React.Component {
    state = {
        view: localStorage.getItem('user-view') === 'user' ? 'user' : 'admin',
    }

    constructor(props) {
        super(props);
    }

    componentDidMount() {
    }

    componentDidUpdate(prevProps, prevState, snapshot) {

    }

    handleChange (value)  {
        const view = value ? 'admin' : 'user';
        localStorage.setItem('user-view', view);
        this.setState({view: view});
        if (view === 'user') {
            window.location = `${CLIENT_BASE_URL}?g=modules&n=dashboard&m=module_Personal_Information`;
        } else if (this.props.userLevel === 'Manager') {
            window.location = `${CLIENT_BASE_URL}?g=modules&n=dashboard&m=module_Personal_Information`;
        } else if (this.props.userLevel === 'Admin') {
            window.location = `${CLIENT_BASE_URL}?g=admin&n=dashboard&m=admin_Admin`;
        }

        //handleViewChange(view);

    };

    render() {
        return (
            <ConfigProvider
                theme={{
                    components: {
                        Switch: {
                            handleSize: 27, // Size of the inner circle (handle)
                            trackHeight: 30, // Height of the switch
                            borderRadius: 2, // Square border (use 0 for a perfect square)
                            backgroundColor: "#f39c05",
                        },
                    },
                }}
            >
                <Switch
                    className="custom-switch"
                    checkedChildren={this.props.userLevel === 'Admin'? 'Admin View': 'Manager View'}
                    unCheckedChildren="Employee View"
                    defaultChecked style={{ padding: "0 16px", width: '100%', borderRadius: 0, fontSize: 18 }}
                    checked={this.state.view === 'admin'}  onChange={(event) => this.handleChange(event) }
                />
            </ConfigProvider>
        );
    }

}

export default UserViewSwitch;
