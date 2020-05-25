import * as React from 'react';
import * as PropTypes from 'prop-types';
import { CheckboxChangeEvent } from './Checkbox';
import { ConfigConsumerProps } from '../config-provider';
export declare type CheckboxValueType = string | number | boolean;
export interface CheckboxOptionType {
    label: React.ReactNode;
    value: CheckboxValueType;
    style?: React.CSSProperties;
    disabled?: boolean;
    onChange?: (e: CheckboxChangeEvent) => void;
}
export interface AbstractCheckboxGroupProps {
    prefixCls?: string;
    className?: string;
    options?: Array<CheckboxOptionType | string>;
    disabled?: boolean;
    style?: React.CSSProperties;
}
export interface CheckboxGroupProps extends AbstractCheckboxGroupProps {
    name?: string;
    defaultValue?: Array<CheckboxValueType>;
    value?: Array<CheckboxValueType>;
    onChange?: (checkedValue: Array<CheckboxValueType>) => void;
}
export interface CheckboxGroupState {
    value: CheckboxValueType[];
    registeredValues: CheckboxValueType[];
}
export interface CheckboxGroupContext {
    toggleOption?: (option: CheckboxOptionType) => void;
    value?: any;
    disabled?: boolean;
}
export declare const GroupContext: React.Context<CheckboxGroupContext | null>;
declare class CheckboxGroup extends React.PureComponent<CheckboxGroupProps, CheckboxGroupState> {
    static defaultProps: {
        options: never[];
    };
    static propTypes: {
        defaultValue: PropTypes.Requireable<any[]>;
        value: PropTypes.Requireable<any[]>;
        options: PropTypes.Validator<any[]>;
        onChange: PropTypes.Requireable<(...args: any[]) => any>;
    };
    static getDerivedStateFromProps(nextProps: CheckboxGroupProps): {
        value: CheckboxValueType[];
    } | null;
    constructor(props: CheckboxGroupProps);
    getOptions(): CheckboxOptionType[];
    cancelValue: (value: string) => void;
    registerValue: (value: string) => void;
    toggleOption: (option: CheckboxOptionType) => void;
    renderGroup: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
export default CheckboxGroup;
