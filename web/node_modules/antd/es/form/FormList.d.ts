import * as React from 'react';
import { StoreValue } from 'rc-field-form/lib/interface';
interface FieldData {
    name: number;
    key: number;
    fieldKey: number;
}
interface Operation {
    add: (defaultValue?: StoreValue) => void;
    remove: (index: number) => void;
    move: (from: number, to: number) => void;
}
interface FormListProps {
    name: string | number | (string | number)[];
    children: (fields: FieldData[], operation: Operation) => React.ReactNode;
}
declare const FormList: React.FC<FormListProps>;
export default FormList;
