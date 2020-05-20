import * as React from 'react';
import { ConfigConsumerProps } from '../config-provider';
export declare type SwitchSize = 'small' | 'default';
export declare type SwitchChangeEventHandler = (checked: boolean, event: MouseEvent) => void;
export declare type SwitchClickEventHandler = SwitchChangeEventHandler;
export interface SwitchProps {
    prefixCls?: string;
    size?: SwitchSize;
    className?: string;
    checked?: boolean;
    defaultChecked?: boolean;
    onChange?: SwitchChangeEventHandler;
    onClick?: SwitchClickEventHandler;
    checkedChildren?: React.ReactNode;
    unCheckedChildren?: React.ReactNode;
    disabled?: boolean;
    loading?: boolean;
    autoFocus?: boolean;
    style?: React.CSSProperties;
    title?: string;
}
export default class Switch extends React.Component<SwitchProps, {}> {
    static __ANT_SWITCH: boolean;
    private rcSwitch;
    constructor(props: SwitchProps);
    saveSwitch: (node: any) => void;
    focus(): void;
    blur(): void;
    renderSwitch: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
