import * as React from 'react';
import { ColProps } from '../grid/col';
import { ValidateStatus } from './FormItem';
interface FormItemInputMiscProps {
    prefixCls: string;
    children: React.ReactNode;
    errors: React.ReactNode[];
    hasFeedback?: boolean;
    validateStatus?: ValidateStatus;
    onDomErrorVisibleChange: (visible: boolean) => void;
}
export interface FormItemInputProps {
    wrapperCol?: ColProps;
    help?: React.ReactNode;
    extra?: React.ReactNode;
}
declare const FormItemInput: React.FC<FormItemInputProps & FormItemInputMiscProps>;
export default FormItemInput;
