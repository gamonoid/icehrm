import * as React from 'react';
import { List } from 'rc-field-form';
import { FormProps as RcFormProps } from 'rc-field-form/lib/Form';
import { ColProps } from '../grid/col';
import { FormLabelAlign } from './interface';
import { useForm, FormInstance } from './util';
import { SizeType } from '../config-provider/SizeContext';
export declare type FormLayout = 'horizontal' | 'inline' | 'vertical';
export interface FormProps extends Omit<RcFormProps, 'form'> {
    prefixCls?: string;
    hideRequiredMark?: boolean;
    colon?: boolean;
    name?: string;
    layout?: FormLayout;
    labelAlign?: FormLabelAlign;
    labelCol?: ColProps;
    wrapperCol?: ColProps;
    form?: FormInstance;
    size?: SizeType;
    scrollToFirstError?: boolean;
}
declare const Form: React.ForwardRefExoticComponent<FormProps & React.RefAttributes<FormInstance>>;
export { useForm, List, FormInstance };
export default Form;
