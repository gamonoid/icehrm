import * as React from 'react';
import { ColumnsType, StickyOffsets, ColumnType, GetComponentProps } from '../interface';
export interface HeaderProps<RecordType> {
    columns: ColumnsType<RecordType>;
    flattenColumns: ColumnType<RecordType>[];
    stickyOffsets: StickyOffsets;
    onHeaderRow: GetComponentProps<ColumnType<RecordType>[]>;
}
declare function Header<RecordType>({ stickyOffsets, columns, flattenColumns, onHeaderRow, }: HeaderProps<RecordType>): React.ReactElement;
export default Header;
