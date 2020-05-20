import * as React from 'react';
import { ColProps } from '../grid/col';
import { FormLabelAlign } from './interface';
export interface FormItemLabelProps {
    colon?: boolean;
    htmlFor?: string;
    label?: React.ReactNode;
    labelAlign?: FormLabelAlign;
    labelCol?: ColProps;
}
declare const FormItemLabel: React.FC<FormItemLabelProps & {
    required?: boolean;
    prefixCls: string;
}>;
export default FormItemLabel;
