function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/**
 * TODO: 4.0
 * - remove `dataSource`
 * - `size` not work with customizeInput
 * - customizeInput not feedback `ENTER` key since accessibility enhancement
 */
import * as React from 'react';
import toArray from "rc-util/es/Children/toArray";
import classNames from 'classnames';
import omit from 'omit.js';
import Select from '../select';
import { ConfigConsumer } from '../config-provider';
import warning from '../_util/warning';
var Option = Select.Option;
var InternalSelect = Select;

function isSelectOptionOrSelectOptGroup(child) {
  return child && child.type && (child.type.isSelectOption || child.type.isSelectOptGroup);
}

var AutoComplete = function AutoComplete(props, ref) {
  var customizePrefixCls = props.prefixCls,
      className = props.className,
      children = props.children,
      dataSource = props.dataSource;
  var childNodes = toArray(children);
  var selectRef = React.useRef();
  React.useImperativeHandle(ref, function () {
    return selectRef.current;
  }); // ============================= Input =============================

  var customizeInput;

  if (childNodes.length === 1 && React.isValidElement(childNodes[0]) && !isSelectOptionOrSelectOptGroup(childNodes[0])) {
    customizeInput = childNodes[0];
  }

  var getInputElement = function getInputElement() {
    return customizeInput;
  }; // ============================ Options ============================


  var optionChildren; // [Legacy] convert `children` or `dataSource` into option children

  if (childNodes.length && isSelectOptionOrSelectOptGroup(childNodes[0])) {
    optionChildren = children;
  } else {
    optionChildren = dataSource ? dataSource.map(function (item) {
      if (React.isValidElement(item)) {
        return item;
      }

      switch (_typeof(item)) {
        case 'string':
          return /*#__PURE__*/React.createElement(Option, {
            key: item,
            value: item
          }, item);

        case 'object':
          {
            var optionValue = item.value;
            return /*#__PURE__*/React.createElement(Option, {
              key: optionValue,
              value: optionValue
            }, item.text);
          }

        default:
          throw new Error('AutoComplete[dataSource] only supports type `string[] | Object[]`.');
      }
    }) : [];
  } // ============================ Warning ============================


  React.useEffect(function () {
    warning(!('dataSource' in props), 'AutoComplete', '`dataSource` is deprecated, please use `options` instead.');
    warning(!customizeInput || !('size' in props), 'AutoComplete', 'You need to control style self instead of setting `size` when using customize input.');
  }, []);
  return /*#__PURE__*/React.createElement(ConfigConsumer, null, function (_ref) {
    var getPrefixCls = _ref.getPrefixCls;
    var prefixCls = getPrefixCls('select', customizePrefixCls);
    return /*#__PURE__*/React.createElement(InternalSelect, _extends({
      ref: selectRef
    }, omit(props, ['dataSource']), {
      prefixCls: prefixCls,
      className: classNames(className, "".concat(prefixCls, "-auto-complete")),
      mode: Select.SECRET_COMBOBOX_MODE_DO_NOT_USE,
      getInputElement: getInputElement
    }), optionChildren);
  });
};

var RefAutoComplete = React.forwardRef(AutoComplete);
RefAutoComplete.Option = Option;
export default RefAutoComplete;