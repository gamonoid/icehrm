import React from 'react';
import { Space, Modal, Select } from 'antd';

class EmployeeSelect extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            modalOpen: true,
            selectedEmployeeName:'',
            selectedEmployeeImage:'',
        };
    }

    closeModal() {
        this.setState({modalOpen: false});
    }

    onChange(value)  {
        this.setState({selectedEmployeeName: value});
        this.setState({selectedEmployeeImage: this.findEmployeeImage(value)});
    };

    findEmployeeImage(name) {
        const found = window.editorEmployees.find((element) => element.value === name);
        return found ? found.image : '';
    }

    saveSelection() {
        this.setState({modalOpen: false});
        if (this.state.selectedEmployeeName !== '') {
            this.props.element.src = this.state.selectedEmployeeImage;
            this.props.element.title = this.state.selectedEmployeeName;
        } else {
            this.props.element.src = BASE_URL+'images/user-icon.png';
            this.props.element.title = 'Not assigned';
        }

    }

    render() {
        const { value } = this.props;

        return (
            <Modal
                title="Select an employee"
                style={{ top: 100 }}
                visible={this.state.modalOpen}
                onOk={() => this.saveSelection()}
                onCancel={() => this.closeModal()}
            >
                <Space  style={{ width: '100%' }}>
                    <Select
                        style={{ width: '100%' }}
                        showSearch
                        placeholder="Select an employee"
                        optionFilterProp="children"
                        onChange={(value) => {this.onChange(value)}}
                        // onSearch={onSearch}
                        filterOption={(input, option) =>
                            (option?.label ?? '').toLowerCase().includes(input.toLowerCase())
                        }
                        options={window.editorEmployees}
                    />
                </Space>
            </Modal>

        );
    }

}

export default EmployeeSelect;
