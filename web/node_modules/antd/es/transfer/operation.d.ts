import * as React from 'react';
export interface TransferOperationProps {
    className?: string;
    leftArrowText?: string;
    rightArrowText?: string;
    moveToLeft?: React.MouseEventHandler<HTMLButtonElement>;
    moveToRight?: React.MouseEventHandler<HTMLButtonElement>;
    leftActive?: boolean;
    rightActive?: boolean;
    style?: React.CSSProperties;
    disabled?: boolean;
    direction?: 'ltr' | 'rtl';
}
declare const Operation: ({ disabled, moveToLeft, moveToRight, leftArrowText, rightArrowText, leftActive, rightActive, className, style, direction, }: TransferOperationProps) => JSX.Element;
export default Operation;
