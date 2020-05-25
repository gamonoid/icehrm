import * as React from 'react';
import { Store, FormInstance, FieldData, ValidateMessages, Callbacks } from './interface';
declare type BaseFormProps = Omit<React.FormHTMLAttributes<HTMLFormElement>, 'onSubmit'>;
declare type RenderProps = (values: Store, form: FormInstance) => JSX.Element | React.ReactNode;
export interface FormProps extends BaseFormProps {
    initialValues?: Store;
    form?: FormInstance;
    children?: RenderProps | React.ReactNode;
    component?: false | string | React.FC<any> | React.ComponentClass<any>;
    fields?: FieldData[];
    name?: string;
    validateMessages?: ValidateMessages;
    onValuesChange?: Callbacks['onValuesChange'];
    onFieldsChange?: Callbacks['onFieldsChange'];
    onFinish?: Callbacks['onFinish'];
    onFinishFailed?: Callbacks['onFinishFailed'];
}
declare const Form: React.ForwardRefRenderFunction<FormInstance, FormProps>;
export default Form;
