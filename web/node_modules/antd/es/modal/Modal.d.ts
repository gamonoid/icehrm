import * as React from 'react';
import useModal from './useModal';
import { ButtonType, ButtonProps } from '../button/button';
import { ConfigConsumerProps } from '../config-provider';
export declare const destroyFns: Array<() => void>;
export interface ModalProps {
    /** 对话框是否可见 */
    visible?: boolean;
    /** 确定按钮 loading */
    confirmLoading?: boolean;
    /** 标题 */
    title?: React.ReactNode | string;
    /** 是否显示右上角的关闭按钮 */
    closable?: boolean;
    /** 点击确定回调 */
    onOk?: (e: React.MouseEvent<HTMLElement>) => void;
    /** 点击模态框右上角叉、取消按钮、Props.maskClosable 值为 true 时的遮罩层或键盘按下 Esc 时的回调 */
    onCancel?: (e: React.MouseEvent<HTMLElement>) => void;
    afterClose?: () => void;
    /** 垂直居中 */
    centered?: boolean;
    /** 宽度 */
    width?: string | number;
    /** 底部内容 */
    footer?: React.ReactNode;
    /** 确认按钮文字 */
    okText?: React.ReactNode;
    /** 确认按钮类型 */
    okType?: ButtonType;
    /** 取消按钮文字 */
    cancelText?: React.ReactNode;
    /** 点击蒙层是否允许关闭 */
    maskClosable?: boolean;
    /** 强制渲染 Modal */
    forceRender?: boolean;
    okButtonProps?: ButtonProps;
    cancelButtonProps?: ButtonProps;
    destroyOnClose?: boolean;
    style?: React.CSSProperties;
    wrapClassName?: string;
    maskTransitionName?: string;
    transitionName?: string;
    className?: string;
    getContainer?: string | HTMLElement | getContainerFunc | false | null;
    zIndex?: number;
    bodyStyle?: React.CSSProperties;
    maskStyle?: React.CSSProperties;
    mask?: boolean;
    keyboard?: boolean;
    wrapProps?: any;
    prefixCls?: string;
    closeIcon?: React.ReactNode;
}
declare type getContainerFunc = () => HTMLElement;
export interface ModalFuncProps {
    prefixCls?: string;
    className?: string;
    visible?: boolean;
    title?: React.ReactNode;
    content?: React.ReactNode;
    onOk?: (...args: any[]) => any;
    onCancel?: (...args: any[]) => any;
    okButtonProps?: ButtonProps;
    cancelButtonProps?: ButtonProps;
    centered?: boolean;
    width?: string | number;
    okText?: React.ReactNode;
    okType?: ButtonType;
    cancelText?: React.ReactNode;
    icon?: React.ReactNode;
    mask?: boolean;
    maskClosable?: boolean;
    zIndex?: number;
    okCancel?: boolean;
    style?: React.CSSProperties;
    maskStyle?: React.CSSProperties;
    type?: string;
    keyboard?: boolean;
    getContainer?: string | HTMLElement | getContainerFunc | false | null;
    autoFocusButton?: null | 'ok' | 'cancel';
    transitionName?: string;
    maskTransitionName?: string;
}
export interface ModalLocale {
    okText: string;
    cancelText: string;
    justOkText: string;
}
export default class Modal extends React.Component<ModalProps, {}> {
    static destroyAll: () => void;
    static useModal: typeof useModal;
    static defaultProps: {
        width: number;
        transitionName: string;
        maskTransitionName: string;
        confirmLoading: boolean;
        visible: boolean;
        okType: "link" | "default" | "primary" | "ghost" | "dashed" | "danger";
    };
    handleCancel: (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => void;
    handleOk: (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => void;
    renderFooter: (locale: ModalLocale) => JSX.Element;
    renderModal: ({ getPopupContainer: getContextPopupContainer, getPrefixCls, direction, }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
export {};
