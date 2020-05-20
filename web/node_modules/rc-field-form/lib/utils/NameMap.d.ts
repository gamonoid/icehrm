import { InternalNamePath } from '../interface';
interface KV<T> {
    key: InternalNamePath;
    value: T;
}
/**
 * NameMap like a `Map` but accepts `string[]` as key.
 */
declare class NameMap<T> {
    private list;
    set(key: InternalNamePath, value: T): void;
    get(key: InternalNamePath): T;
    update(key: InternalNamePath, updater: (origin: T) => T | null): void;
    delete(key: InternalNamePath): void;
    map<U>(callback: (kv: KV<T>) => U): U[];
    toJSON(): {
        [name: string]: T;
    };
}
export default NameMap;
