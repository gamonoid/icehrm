/// <reference types="react" />
import { HeaderProps } from './Header';
export interface FixedHeaderProps<RecordType> extends HeaderProps<RecordType> {
    colWidths: number[];
    columCount: number;
    direction: 'ltr' | 'rtl';
}
declare function FixedHeader<RecordType>({ columns, flattenColumns, colWidths, columCount, stickyOffsets, direction, ...props }: FixedHeaderProps<RecordType>): JSX.Element;
export default FixedHeader;
