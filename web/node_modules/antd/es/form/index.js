import InternalForm, { useForm } from './Form';
import Item from './FormItem';
import List from './FormList';
import { FormProvider } from './context';
import warning from '../_util/warning';
var Form = InternalForm;
Form.Item = Item;
Form.List = List;
Form.useForm = useForm;
Form.Provider = FormProvider;

Form.create = function () {
  warning(false, 'Form', 'antd v4 removed `Form.create`. Please remove or use `@ant-design/compatible` instead.');
};

export default Form;