import * as React from 'react';
import { FormProviderProps as RcFormProviderProps } from 'rc-field-form/lib/FormContext';
import { ColProps } from '../grid/col';
import { FormLabelAlign } from './interface';
/**
 * Form Context
 * Set top form style and pass to Form Item usage.
 */
export interface FormContextProps {
    vertical: boolean;
    name?: string;
    colon?: boolean;
    labelAlign?: FormLabelAlign;
    labelCol?: ColProps;
    wrapperCol?: ColProps;
}
export declare const FormContext: React.Context<FormContextProps>;
/**
 * Form Item Context
 * Used for Form noStyle Item error collection
 */
export interface FormItemContextProps {
    updateItemErrors: (name: string, errors: string[]) => void;
}
export declare const FormItemContext: React.Context<FormItemContextProps>;
/**
 * Form Provider
 *
 */
export interface FormProviderProps extends Omit<RcFormProviderProps, 'validateMessages'> {
}
export declare const FormProvider: React.FC<FormProviderProps>;
