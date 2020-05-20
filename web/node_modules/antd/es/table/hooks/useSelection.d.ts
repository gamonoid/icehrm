import { TableRowSelection, Key, GetRowKey, TableLocale, TransformColumns, ExpandType, GetPopupContainer } from '../interface';
export declare const SELECTION_ALL = "SELECT_ALL";
export declare const SELECTION_INVERT = "SELECT_INVERT";
interface UseSelectionConfig<RecordType> {
    prefixCls: string;
    pageData: RecordType[];
    data: RecordType[];
    getRowKey: GetRowKey<RecordType>;
    getRecordByKey: (key: Key) => RecordType;
    expandType: ExpandType;
    childrenColumnName: string;
    expandIconColumnIndex?: number;
    locale: TableLocale;
    getPopupContainer?: GetPopupContainer;
}
export default function useSelection<RecordType>(rowSelection: TableRowSelection<RecordType> | undefined, config: UseSelectionConfig<RecordType>): [TransformColumns<RecordType>, Set<Key>];
export {};
