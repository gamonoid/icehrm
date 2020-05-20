/// <reference types="react" />
import { CellType, StickyOffsets, ColumnType, CustomizeComponent, GetComponentProps } from '../interface';
export interface RowProps<RecordType> {
    cells: CellType<RecordType>[];
    stickyOffsets: StickyOffsets;
    flattenColumns: ColumnType<RecordType>[];
    rowComponent: CustomizeComponent;
    cellComponent: CustomizeComponent;
    onHeaderRow: GetComponentProps<ColumnType<RecordType>[]>;
    index: number;
}
declare function HeaderRow<RecordType>({ cells, stickyOffsets, flattenColumns, rowComponent: RowComponent, cellComponent: CellComponent, onHeaderRow, index, }: RowProps<RecordType>): JSX.Element;
declare namespace HeaderRow {
    var displayName: string;
}
export default HeaderRow;
