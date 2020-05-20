import * as React from 'react';
import { ColumnsType, ColumnType, Key, GetRowKey, TriggerEventHandler, RenderExpandIcon } from '../interface';
export declare function convertChildrenToColumns<RecordType>(children: React.ReactNode): ColumnsType<RecordType>;
/**
 * Parse `columns` & `children` into `columns`.
 */
declare function useColumns<RecordType>({ prefixCls, columns, children, expandable, expandedKeys, getRowKey, onTriggerExpand, expandIcon, rowExpandable, expandIconColumnIndex, direction, }: {
    prefixCls?: string;
    columns?: ColumnsType<RecordType>;
    children?: React.ReactNode;
    expandable: boolean;
    expandedKeys: Set<Key>;
    getRowKey: GetRowKey<RecordType>;
    onTriggerExpand: TriggerEventHandler<RecordType>;
    expandIcon?: RenderExpandIcon<RecordType>;
    rowExpandable?: (record: RecordType) => boolean;
    expandIconColumnIndex?: number;
    direction?: 'ltr' | 'rtl';
}, transformColumns: (columns: ColumnsType<RecordType>) => ColumnsType<RecordType>): [ColumnsType<RecordType>, ColumnType<RecordType>[]];
export default useColumns;
