import React, {Component} from 'react';
import {
    Form,
    Modal,
    Input,
    Button,
    message,
} from 'antd';
import CustomAction from '../api/CustomAction';

class UpdatePasswordModal extends React.Component {

    state = {
        loading: false,
        passwordHasError: false,
        passwordState: { hasFeedback: false, validateStatus:'', help:'Password must include at least one number, one lowercase letter, one uppercase letter and a symbol' },
        confirmationHasError: false,
        confirmationState: { hasFeedback: false, validateStatus:'', help:'' },
    };

    constructor(props) {
        super(props);
        this.formRef = React.createRef();
        this.customAction = new CustomAction(this.props.adapter);
    }

    componentDidMount() {
        message.config({
            top: 40,
        });
    }

    clearConfirmFeedback = () => {
        this.setState({confirmationHasError: false});
        this.setState({
            confirmationState: {
                hasFeedback :  false,
                validateStatus:'',
                help:'',
            }
        });
    }

    updatePasswordState(value) {
        const passwordValidationResult = this.validatePassword(value);
        if (passwordValidationResult !== null) {
            this.setState({passwordHasError: true});
            this.setState({
                passwordState: {
                    hasFeedback :  true,
                    validateStatus:'error',
                    help:passwordValidationResult,
                }
            });

            return false;
        } else {
            this.setState({passwordHasError: false});
            this.setState({
                passwordState: {
                    hasFeedback :  true,
                    validateStatus:'success',
                    help:'',
                }
            });
        }

        return true;
    }

    updateConfirmPasswordState(values) {
        if (values.confirm !== values.new) {
            this.setState({confirmationHasError: true});
            this.setState({
                confirmationState: {
                    hasFeedback :  true,
                    validateStatus:'error',
                    help:'Passwords don\'t match',
                }
            });

            return false;
        } else {
            this.setState({confirmationHasError: false});
            this.setState({
                confirmationState: {
                    hasFeedback :  false,
                    validateStatus:'',
                    help:'',
                }
            });
        }

        return true;
    }

    handleOk = () => {
        const from = this.formRef.current;
        from
            .validateFields()
            .then((values) => {
                if (this.updatePasswordState(values.new) && this.updateConfirmPasswordState(values)) {
                    this.updatePassword(values.current, values.new)
                      .then((response) => {
                        const data = response.data;
                        if (data.status === 'SUCCESS') {
                            this.handleCancel();
                            message.success(this.props.adapter.gt('Password updated'));
                        } else {
                            message.error(
                              `${this.props.adapter.gt('Error updating password')}: ${this.props.adapter.gt(data.data)}`
                            );
                        }
                    }).catch((error) => {
                        message.error(
                          `${this.props.adapter.gt('Error updating password')}`
                        );
                        console.log(error.message);
                    });
                }
            })
            .catch((info) => {
                this.setState({ loading: false });
            });
    }

    handleCancel = () => {
        if (this.formRef.current) {
            this.formRef.current.resetFields();
        }
        this.props.closeModal();
    }

    updatePassword = (oldPassword, newPassword) => {
        const req = { current: oldPassword ? oldPassword : '', pwd: newPassword };
        const reqJson = JSON.stringify(req);

        const callBackData = [];
        callBackData.callBackData = [];
        callBackData.callBackSuccess = 'changePasswordSuccessCallBack';
        callBackData.callBackFail = 'changePasswordFailCallBack';

        return this.customAction.execute('changePassword', 'modules=employees', reqJson);

    }

    validatePassword = (password) => {
        if (password.length < 8) {
            return this.props.adapter.gt('Password too short');
        }

        if (password.length > 30) {
            return this.props.adapter.gt('Password too long');
        }

        const numberTester = /.*[0-9]+.*$/;
        if (!password.match(numberTester)) {
            return this.props.adapter.gt('Password must include at least one number');
        }

        const lowerTester = /.*[a-z]+.*$/;
        if (!password.match(lowerTester)) {
            return this.props.adapter.gt('Password must include at least one lowercase letter');
        }

        const upperTester = /.*[A-Z]+.*$/;
        if (!password.match(upperTester)) {
            return this.props.adapter.gt('Password must include at least one uppercase letter');
        }

        const symbolTester = /.*[\W]+.*$/;
        if (!password.match(symbolTester)) {
            return this.props.adapter.gt('Password must include at least one symbol');
        }

        return null;
    }

    render() {
        const layout = {
            labelCol: { span: 8 },
            wrapperCol: { span: 16 },
        };

        return (
            <Modal
                visible={this.props.visible}
                title="Update Password"
                onOk={this.handleOk}
                onCancel={this.handleCancel}
                footer={[
                    <Button key="back" onClick={this.handleCancel}>
                        {this.props.adapter.gt('Cancel')}
                    </Button>,
                    <Button key="submit" type="primary" loading={this.state.loading} onClick={this.handleOk}>
                        {this.props.adapter.gt('Update')}
                    </Button>,
                ]}
            >
                <Form {...layout} ref={this.formRef}>
                    <Form.Item label="Current Password" key="current" name="current" >
                        <Input.Password placeholder="current password"/>
                    </Form.Item>
                    { this.state.passwordHasError &&
                        <Form.Item label="New Password" key="new" name="new" {...this.state.passwordState}>
                            <Input.Password placeholder="new password" onChange={(event) => this.updatePasswordState(event.target.value)}/>
                        </Form.Item>
                    }
                    { !this.state.passwordHasError &&
                    <Form.Item label="New Password" key="new" name="new" {...this.state.passwordState}>
                        <Input.Password placeholder="new password" onChange={(event) => this.updatePasswordState(event.target.value)}/>
                    </Form.Item>
                    }
                    { this.state.confirmationHasError &&
                    <Form.Item label="Confirm Password" key="confirm" name="confirm" {...this.state.confirmationState}>
                        <Input.Password placeholder="confirm password" onChange={(event) => this.clearConfirmFeedback()}/>
                    </Form.Item>
                    }
                    { !this.state.confirmationHasError &&
                    <Form.Item label="Confirm Password" key="confirm" name="confirm" >
                        <Input.Password placeholder="confirm password" onChange={(event) => this.clearConfirmFeedback()}/>
                    </Form.Item>
                    }

                </Form>
            </Modal>
        )
    }
}

export default UpdatePasswordModal;
