function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

import CalendarLocale from "rc-picker/es/locale/az_AZ";
import TimePickerLocale from '../../time-picker/locale/az_AZ';
var locale = {
  lang: _extends({
    placeholder: 'Tarix seçin',
    rangePlaceholder: ['Başlama tarixi', 'Bitmə tarixi']
  }, CalendarLocale),
  timePickerLocale: _extends({}, TimePickerLocale)
};
export default locale;