import * as React from 'react';
import RcSelect, { Option, SelectProps as RcSelectProps } from 'rc-select';
import { ConfigConsumerProps } from '../config-provider';
import { SizeType } from '../config-provider/SizeContext';
declare type RawValue = string | number;
export declare type OptionType = typeof Option;
export interface LabeledValue {
    key?: string;
    value: RawValue;
    label: React.ReactNode;
}
export declare type SelectValue = RawValue | RawValue[] | LabeledValue | LabeledValue[];
export interface InternalSelectProps<VT> extends Omit<RcSelectProps<VT>, 'mode'> {
    suffixIcon?: React.ReactNode;
    size?: SizeType;
    mode?: 'multiple' | 'tags' | 'SECRET_COMBOBOX_MODE_DO_NOT_USE';
    bordered?: boolean;
}
export interface SelectProps<VT> extends Omit<InternalSelectProps<VT>, 'inputIcon' | 'mode' | 'getInputElement' | 'backfill'> {
    mode?: 'multiple' | 'tags';
}
declare class Select<ValueType extends SelectValue = SelectValue> extends React.Component<SelectProps<ValueType>> {
    static Option: import("rc-select/lib/Option").OptionFC;
    static OptGroup: import("rc-select/lib/OptGroup").OptionGroupFC;
    static SECRET_COMBOBOX_MODE_DO_NOT_USE: string;
    static defaultProps: {
        transitionName: string;
        choiceTransitionName: string;
        bordered: boolean;
    };
    selectRef: React.RefObject<RcSelect<ValueType>>;
    focus: () => void;
    blur: () => void;
    getMode: () => "multiple" | "tags" | "SECRET_COMBOBOX_MODE_DO_NOT_USE" | "combobox" | undefined;
    renderSelect: ({ getPopupContainer: getContextPopupContainer, getPrefixCls, renderEmpty, direction, }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
export default Select;
