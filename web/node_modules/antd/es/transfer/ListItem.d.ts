import * as React from 'react';
import { TransferItem } from '.';
declare type ListItemProps = {
    renderedText?: string | number;
    renderedEl: React.ReactNode;
    disabled?: boolean;
    checked?: boolean;
    prefixCls: string;
    onClick: (item: TransferItem) => void;
    item: TransferItem;
};
declare const _default: React.MemoExoticComponent<(props: ListItemProps) => JSX.Element>;
export default _default;
