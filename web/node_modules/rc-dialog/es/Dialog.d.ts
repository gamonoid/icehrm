import * as React from 'react';
import IDialogPropTypes from './IDialogPropTypes';
export interface IDialogChildProps extends IDialogPropTypes {
    getOpenCount: () => number;
    switchScrollingEffect?: () => void;
}
export default class Dialog extends React.Component<IDialogChildProps, any> {
    static defaultProps: {
        className: string;
        mask: boolean;
        visible: boolean;
        keyboard: boolean;
        closable: boolean;
        maskClosable: boolean;
        destroyOnClose: boolean;
        prefixCls: string;
        focusTriggerAfterClose: boolean;
    };
    private inTransition;
    private titleId;
    private openTime;
    private lastOutSideFocusNode;
    private wrap;
    private dialog;
    private sentinelStart;
    private sentinelEnd;
    private dialogMouseDown;
    private timeoutId;
    private switchScrollingEffect;
    constructor(props: IDialogChildProps);
    componentDidMount(): void;
    componentDidUpdate(prevProps: IDialogPropTypes): void;
    componentWillUnmount(): void;
    tryFocus(): void;
    onAnimateLeave: () => void;
    onDialogMouseDown: () => void;
    onMaskMouseUp: React.MouseEventHandler<HTMLDivElement>;
    onMaskClick: (e: React.MouseEvent<HTMLDivElement, MouseEvent>) => void;
    onKeyDown: (e: React.KeyboardEvent<HTMLDivElement>) => void;
    getDialogElement: () => JSX.Element;
    getZIndexStyle: () => any;
    getWrapStyle: () => any;
    getMaskStyle: () => any;
    getMaskElement: () => JSX.Element | undefined;
    getMaskTransitionName: () => string | undefined;
    getTransitionName: () => string | undefined;
    close: (e: any) => void;
    saveRef: (name: string) => (node: any) => void;
    render(): JSX.Element;
}
