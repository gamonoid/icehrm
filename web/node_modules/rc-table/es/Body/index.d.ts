import * as React from 'react';
import { GetRowKey, StickyOffsets, Key, GetComponentProps } from '../interface';
export interface BodyProps<RecordType> {
    data: RecordType[];
    getRowKey: GetRowKey<RecordType>;
    measureColumnWidth: boolean;
    stickyOffsets: StickyOffsets;
    expandedKeys: Set<Key>;
    onRow: GetComponentProps<RecordType>;
    rowExpandable: (record: RecordType) => boolean;
    emptyNode: React.ReactNode;
    childrenColumnName: string;
}
declare function Body<RecordType>({ data, getRowKey, measureColumnWidth, stickyOffsets, expandedKeys, onRow, rowExpandable, emptyNode, childrenColumnName, }: BodyProps<RecordType>): JSX.Element;
declare const MemoBody: React.MemoExoticComponent<typeof Body>;
export default MemoBody;
