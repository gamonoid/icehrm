import * as React from 'react';
import { TransformColumns, ColumnsType, Key, ColumnType, SortOrder, ColumnTitleProps, SorterResult, TableLocale } from '../interface';
export interface SortState<RecordType> {
    column: ColumnType<RecordType>;
    key: Key;
    sortOrder: SortOrder | null;
    multiplePriority: number | false;
}
export declare function getSortData<RecordType>(data: RecordType[], sortStates: SortState<RecordType>[], childrenColumnName: string): RecordType[];
interface SorterConfig<RecordType> {
    prefixCls: string;
    columns?: ColumnsType<RecordType>;
    children?: React.ReactNode;
    onSorterChange: (sorterResult: SorterResult<RecordType> | SorterResult<RecordType>[], sortStates: SortState<RecordType>[]) => void;
    sortDirections: SortOrder[];
    tableLocale?: TableLocale;
    showSorterTooltip?: boolean;
}
export default function useFilterSorter<RecordType>({ prefixCls, columns, children, onSorterChange, sortDirections, tableLocale, showSorterTooltip, }: SorterConfig<RecordType>): [TransformColumns<RecordType>, SortState<RecordType>[], ColumnTitleProps<RecordType>, () => SorterResult<RecordType> | SorterResult<RecordType>[]];
export {};
