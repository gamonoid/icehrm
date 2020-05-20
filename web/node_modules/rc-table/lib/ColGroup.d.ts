/// <reference types="react" />
import { ColumnType } from './interface';
export interface ColGroupProps<RecordType> {
    colWidths: (number | string)[];
    columns?: ColumnType<RecordType>[];
    columCount?: number;
}
declare function ColGroup<RecordType>({ colWidths, columns, columCount }: ColGroupProps<RecordType>): JSX.Element;
export default ColGroup;
