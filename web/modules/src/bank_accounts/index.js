import { EmployeeBankAccountAdapter } from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.EmployeeBankAccountAdapter = EmployeeBankAccountAdapter;
window.IceDataPipe = IceDataPipe;

// Native SPA init (mounted by the shell via NativeModuleRegistry).
function init(data) {
  const modJsList = {};
  const a = new EmployeeBankAccountAdapter('EmployeeBankAccount', 'EmployeeBankAccount', '', '');
  a.setObjectTypeName('Bank Account');
  a.setDataPipe(new IceDataPipe(a));
  a.setAccess((data && data.permissions && data.permissions.EmployeeBankAccount) || ['get', 'element', 'save', 'delete']);
  modJsList.tabEmployeeBankAccount = a;
  window.modJs = a;
  window.modJsList = modJsList;
}
window.initModulesBankAccounts = init;
