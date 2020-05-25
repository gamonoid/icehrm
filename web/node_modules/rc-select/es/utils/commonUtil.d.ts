import { RawValueType, GetLabeledValue, LabelValueType, DefaultValueType, FlattenOptionsType } from '../interface/generator';
export declare function toArray<T>(value: T | T[]): T[];
/**
 * Convert outer props value into internal value
 */
export declare function toInnerValue(value: DefaultValueType, { labelInValue, combobox }: {
    labelInValue: boolean;
    combobox: boolean;
}): RawValueType[];
/**
 * Convert internal value into out event value
 */
export declare function toOuterValues<FOT extends FlattenOptionsType>(valueList: RawValueType[], { optionLabelProp, labelInValue, prevValue, options, getLabeledValue, }: {
    optionLabelProp: string;
    labelInValue: boolean;
    getLabeledValue: GetLabeledValue<FOT>;
    options: FOT;
    prevValue: DefaultValueType;
}): RawValueType[] | LabelValueType[];
export declare function removeLastEnabledValue<T extends {
    disabled?: boolean;
}, P extends RawValueType | object>(measureValues: T[], values: P[]): {
    values: P[];
    removedValue: P;
};
export declare const isClient: HTMLElement;
/** Is client side and not jsdom */
export declare const isBrowserClient: HTMLElement;
/** Get unique id for accessibility usage */
export declare function getUUID(): number | string;
