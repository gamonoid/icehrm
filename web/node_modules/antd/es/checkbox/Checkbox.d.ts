import * as React from 'react';
import CheckboxGroup from './Group';
import { ConfigConsumerProps } from '../config-provider';
export interface AbstractCheckboxProps<T> {
    prefixCls?: string;
    className?: string;
    defaultChecked?: boolean;
    checked?: boolean;
    style?: React.CSSProperties;
    disabled?: boolean;
    onChange?: (e: T) => void;
    onClick?: React.MouseEventHandler<HTMLElement>;
    onMouseEnter?: React.MouseEventHandler<HTMLElement>;
    onMouseLeave?: React.MouseEventHandler<HTMLElement>;
    onKeyPress?: React.KeyboardEventHandler<HTMLElement>;
    onKeyDown?: React.KeyboardEventHandler<HTMLElement>;
    value?: any;
    tabIndex?: number;
    name?: string;
    children?: React.ReactNode;
    id?: string;
    autoFocus?: boolean;
}
export interface CheckboxProps extends AbstractCheckboxProps<CheckboxChangeEvent> {
    indeterminate?: boolean;
}
export interface CheckboxChangeEventTarget extends CheckboxProps {
    checked: boolean;
}
export interface CheckboxChangeEvent {
    target: CheckboxChangeEventTarget;
    stopPropagation: () => void;
    preventDefault: () => void;
    nativeEvent: MouseEvent;
}
declare class Checkbox extends React.PureComponent<CheckboxProps, {}> {
    static Group: typeof CheckboxGroup;
    static __ANT_CHECKBOX: boolean;
    static defaultProps: {
        indeterminate: boolean;
    };
    static contextType: React.Context<import("./Group").CheckboxGroupContext | null>;
    context: any;
    private rcCheckbox;
    componentDidMount(): void;
    componentDidUpdate({ value: prevValue }: CheckboxProps): void;
    componentWillUnmount(): void;
    saveCheckbox: (node: any) => void;
    focus(): void;
    blur(): void;
    renderCheckbox: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
export default Checkbox;
