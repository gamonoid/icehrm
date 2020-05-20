import * as React from 'react';
import { NamePath, StoreValue } from './interface';
interface ListField {
    name: number;
    key: number;
}
interface ListOperations {
    add: (defaultValue?: StoreValue) => void;
    remove: (index: number) => void;
    move: (from: number, to: number) => void;
}
interface ListProps {
    name: NamePath;
    children?: (fields: ListField[], operations: ListOperations) => JSX.Element | React.ReactNode;
}
declare const List: React.FunctionComponent<ListProps>;
export default List;
