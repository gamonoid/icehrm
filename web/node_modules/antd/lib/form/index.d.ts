import { Rule } from 'rc-field-form/lib/interface';
import InternalForm, { useForm, FormInstance, FormProps } from './Form';
import Item, { FormItemProps } from './FormItem';
import List from './FormList';
import { FormProvider } from './context';
declare type InternalForm = typeof InternalForm;
interface Form extends InternalForm {
    useForm: typeof useForm;
    Item: typeof Item;
    List: typeof List;
    Provider: typeof FormProvider;
    /** @deprecated Only for warning usage. Do not use. */
    create: () => void;
}
declare const Form: Form;
export { FormInstance, FormProps, FormItemProps, Rule };
export default Form;
