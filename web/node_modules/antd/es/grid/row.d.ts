import * as React from 'react';
import { ConfigConsumerProps } from '../config-provider';
import { Breakpoint, ScreenMap } from '../_util/responsiveObserve';
declare const RowAligns: ["top", "middle", "bottom", "stretch"];
declare const RowJustify: ["start", "end", "center", "space-around", "space-between"];
export declare type Gutter = number | Partial<Record<Breakpoint, number>>;
export interface RowProps extends React.HTMLAttributes<HTMLDivElement> {
    gutter?: Gutter | [Gutter, Gutter];
    align?: typeof RowAligns[number];
    justify?: typeof RowJustify[number];
    prefixCls?: string;
}
export interface RowState {
    screens: ScreenMap;
}
export default class Row extends React.Component<RowProps, RowState> {
    static defaultProps: {
        gutter: number;
    };
    state: RowState;
    token: string;
    componentDidMount(): void;
    componentWillUnmount(): void;
    getGutter(): [number, number];
    renderRow: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
export {};
