import { Key, DataIndex } from '../interface';
export declare function getPathValue<ValueType, ObjectType extends object>(record: ObjectType, path: DataIndex): ValueType;
interface GetColumnKeyColumn {
    key?: Key;
    dataIndex?: DataIndex;
}
export declare function getColumnsKey(columns: GetColumnKeyColumn[]): (string | number)[];
export declare function mergeObject<ReturnObject extends object>(...objects: Partial<ReturnObject>[]): ReturnObject;
export declare function validateValue<T>(val: T): boolean;
export {};
