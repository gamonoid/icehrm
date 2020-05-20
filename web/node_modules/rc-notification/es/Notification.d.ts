import React, { Component } from 'react';
import { NoticeProps } from './Notice';
export interface NoticeContent extends Omit<NoticeProps, 'prefixCls' | 'children'> {
    prefixCls?: string;
    key?: React.Key;
    updateKey?: React.Key;
    content?: React.ReactNode;
}
export declare type NoticeFunc = (noticeProps: NoticeContent) => void;
export declare type HolderReadyCallback = (div: HTMLDivElement, noticeProps: NoticeProps & {
    key: React.Key;
}) => void;
export interface NotificationInstance {
    notice: NoticeFunc;
    removeNotice: (key: React.Key) => void;
    destroy: () => void;
    component: Notification;
    useNotification: () => [NoticeFunc, React.ReactElement];
}
export interface NotificationProps {
    prefixCls?: string;
    className?: string;
    style?: React.CSSProperties;
    transitionName?: string;
    animation?: string | object;
    maxCount?: number;
    closeIcon?: React.ReactNode;
}
interface NotificationState {
    notices: {
        notice: NoticeContent;
        holderCallback?: HolderReadyCallback;
    }[];
}
declare class Notification extends Component<NotificationProps, NotificationState> {
    static newInstance: (properties: NotificationProps & {
        getContainer?: () => HTMLElement;
    }, callback: (instance: NotificationInstance) => void) => void;
    static defaultProps: {
        prefixCls: string;
        animation: string;
        style: {
            top: number;
            left: string;
        };
    };
    state: NotificationState;
    private hookRefs;
    getTransitionName(): string;
    add: (notice: NoticeContent, holderCallback?: HolderReadyCallback) => void;
    remove: (key: string | number) => void;
    render(): JSX.Element;
}
export default Notification;
