import * as React from 'react';
import { ButtonHTMLType } from '../button/button';
import { ButtonGroupProps } from '../button/button-group';
import { ConfigConsumerProps } from '../config-provider';
import { DropDownProps } from './dropdown';
declare type DropdownButtonType = 'primary' | 'ghost' | 'dashed';
export interface DropdownButtonProps extends ButtonGroupProps, DropDownProps {
    type?: DropdownButtonType;
    htmlType?: ButtonHTMLType;
    disabled?: boolean;
    onClick?: React.MouseEventHandler<HTMLButtonElement>;
    icon?: React.ReactNode;
    href?: string;
    children?: React.ReactNode;
    title?: string;
    buttonsRender?: (buttons: React.ReactNode[]) => React.ReactNode[];
}
export default class DropdownButton extends React.Component<DropdownButtonProps, any> {
    static __ANT_BUTTON: boolean;
    static defaultProps: {
        placement: "topLeft" | "topCenter" | "topRight" | "bottomLeft" | "bottomCenter" | "bottomRight" | undefined;
        type: DropdownButtonType;
        buttonsRender: (buttons: React.ReactNode[]) => React.ReactNode[];
    };
    renderButton: ({ getPopupContainer: getContextPopupContainer, getPrefixCls, }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
export {};
