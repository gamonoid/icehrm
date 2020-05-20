import * as React from 'react';
import { FormInstance as RcFormInstance } from 'rc-field-form';
import { ScrollOptions } from './interface';
declare type InternalNamePath = (string | number)[];
/**
 * Always debounce error to avoid [error -> null -> error] blink
 */
export declare function useCacheErrors(errors: React.ReactNode[], changeTrigger: (visible: boolean) => void, directly: boolean): [boolean, React.ReactNode[]];
export declare function toArray<T>(candidate?: T | T[] | false): T[];
export declare function getFieldId(namePath: InternalNamePath, formName?: string): string | undefined;
export interface FormInstance extends RcFormInstance {
    scrollToField: (name: string | number | InternalNamePath, options?: ScrollOptions) => void;
    __INTERNAL__: {
        name?: string;
    };
}
export declare function useForm(form?: FormInstance): [FormInstance];
declare type Updater<ValueType> = (prev?: ValueType) => ValueType;
export declare function useFrameState<ValueType>(defaultValue: ValueType): [ValueType, (updater: Updater<ValueType>) => void];
export {};
