import * as React from 'react';
import { ConfigConsumerProps } from '../config-provider';
export interface StepsProps {
    type?: 'default' | 'navigation';
    className?: string;
    current?: number;
    direction?: 'horizontal' | 'vertical';
    iconPrefix?: string;
    initial?: number;
    labelPlacement?: 'horizontal' | 'vertical';
    prefixCls?: string;
    progressDot?: boolean | Function;
    size?: 'default' | 'small';
    status?: 'wait' | 'process' | 'finish' | 'error';
    style?: React.CSSProperties;
    onChange?: (current: number) => void;
}
export interface StepProps {
    className?: string;
    description?: React.ReactNode;
    icon?: React.ReactNode;
    onClick?: React.MouseEventHandler<HTMLElement>;
    status?: 'wait' | 'process' | 'finish' | 'error';
    disabled?: boolean;
    title?: React.ReactNode;
    subTitle?: React.ReactNode;
    style?: React.CSSProperties;
}
export default class Steps extends React.Component<StepsProps, any> {
    static Step: React.ClassicComponentClass<StepProps>;
    static defaultProps: {
        current: number;
    };
    renderSteps: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
