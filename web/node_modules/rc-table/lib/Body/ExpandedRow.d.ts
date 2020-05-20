import * as React from 'react';
import { CustomizeComponent } from '../interface';
export interface ExpandedRowProps<RecordType> {
    prefixCls: string;
    component: CustomizeComponent;
    cellComponent: CustomizeComponent;
    fixHeader: boolean;
    fixColumn: boolean;
    horizonScroll: boolean;
    componentWidth: number;
    className: string;
    expanded: boolean;
    children: React.ReactNode;
    colSpan: number;
}
declare function ExpandedRow<RecordType>({ prefixCls, children, component: Component, cellComponent, fixHeader, fixColumn, horizonScroll, className, expanded, componentWidth, colSpan, }: ExpandedRowProps<RecordType>): JSX.Element;
export default ExpandedRow;
