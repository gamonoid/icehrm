import React from 'react';
interface PopupInnerProps {
    prefixCls: string;
    className: string;
    hiddenClassName?: string;
    visible?: boolean;
    style?: React.CSSProperties;
    children?: React.ReactNode;
    onMouseEnter?: React.MouseEventHandler<HTMLDivElement>;
    onMouseLeave?: React.MouseEventHandler<HTMLDivElement>;
    onMouseDown?: React.MouseEventHandler<HTMLDivElement>;
    onTouchStart?: React.TouchEventHandler<HTMLDivElement>;
}
declare const RefPopupInner: React.ForwardRefExoticComponent<PopupInnerProps & React.RefAttributes<HTMLDivElement>>;
export default RefPopupInner;
