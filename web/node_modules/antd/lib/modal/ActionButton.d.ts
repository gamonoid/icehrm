import * as React from 'react';
import { ButtonType, ButtonProps } from '../button/button';
export interface ActionButtonProps {
    type?: ButtonType;
    actionFn?: (...args: any[]) => any | PromiseLike<any>;
    closeModal: Function;
    autoFocus?: boolean;
    buttonProps?: ButtonProps;
}
export interface ActionButtonState {
    loading: ButtonProps['loading'];
}
export default class ActionButton extends React.Component<ActionButtonProps, ActionButtonState> {
    timeoutId: number;
    clicked: boolean;
    state: {
        loading: boolean;
    };
    componentDidMount(): void;
    componentWillUnmount(): void;
    handlePromiseOnOk(returnValueOfOnOk?: PromiseLike<any>): void;
    onClick: () => void;
    render(): JSX.Element;
}
