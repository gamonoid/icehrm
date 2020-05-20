import * as React from 'react';
import { ConfigConsumerProps } from '../config-provider';
import ErrorBoundary from './ErrorBoundary';
export interface AlertProps {
    /**
     * Type of Alert styles, options:`success`, `info`, `warning`, `error`
     */
    type?: 'success' | 'info' | 'warning' | 'error';
    /** Whether Alert can be closed */
    closable?: boolean;
    /** Close text to show */
    closeText?: React.ReactNode;
    /** Content of Alert */
    message: React.ReactNode;
    /** Additional content of Alert */
    description?: React.ReactNode;
    /** Callback when close Alert */
    onClose?: React.MouseEventHandler<HTMLButtonElement>;
    /** Trigger when animation ending of Alert */
    afterClose?: () => void;
    /** Whether to show icon */
    showIcon?: boolean;
    style?: React.CSSProperties;
    prefixCls?: string;
    className?: string;
    banner?: boolean;
    icon?: React.ReactNode;
    onMouseEnter?: React.MouseEventHandler<HTMLDivElement>;
    onMouseLeave?: React.MouseEventHandler<HTMLDivElement>;
    onClick?: React.MouseEventHandler<HTMLDivElement>;
}
export interface AlertState {
    closing: boolean;
    closed: boolean;
}
export default class Alert extends React.Component<AlertProps, AlertState> {
    static ErrorBoundary: typeof ErrorBoundary;
    state: {
        closing: boolean;
        closed: boolean;
    };
    handleClose: (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => void;
    animationEnd: () => void;
    renderAlert: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element | null;
    render(): JSX.Element;
}
