"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _react = _interopRequireDefault(require("react"));

var _Input = _interopRequireDefault(require("./Input"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var SingleSelector = function SingleSelector(_ref) {
  var inputElement = _ref.inputElement,
      prefixCls = _ref.prefixCls,
      id = _ref.id,
      inputRef = _ref.inputRef,
      disabled = _ref.disabled,
      autoFocus = _ref.autoFocus,
      autoComplete = _ref.autoComplete,
      accessibilityIndex = _ref.accessibilityIndex,
      mode = _ref.mode,
      open = _ref.open,
      values = _ref.values,
      placeholder = _ref.placeholder,
      tabIndex = _ref.tabIndex,
      showSearch = _ref.showSearch,
      searchValue = _ref.searchValue,
      activeValue = _ref.activeValue,
      onInputKeyDown = _ref.onInputKeyDown,
      onInputMouseDown = _ref.onInputMouseDown,
      onInputChange = _ref.onInputChange,
      onInputPaste = _ref.onInputPaste;
  var combobox = mode === 'combobox';
  var inputEditable = combobox || showSearch && open;
  var item = values[0];

  var getDisplayValue = function getDisplayValue(value) {
    return value === null ? '' : String(value);
  };

  var inputValue = searchValue;

  if (combobox) {
    inputValue = item ? getDisplayValue(item.value) : activeValue || searchValue;
  } // Not show text when closed expect combobox mode


  var hasTextInput = mode !== 'combobox' && !open ? false : !!inputValue;
  return _react.default.createElement(_react.default.Fragment, null, _react.default.createElement("span", {
    className: "".concat(prefixCls, "-selection-search")
  }, _react.default.createElement(_Input.default, {
    ref: inputRef,
    prefixCls: prefixCls,
    id: id,
    open: open,
    inputElement: inputElement,
    disabled: disabled,
    autoFocus: autoFocus,
    autoComplete: autoComplete,
    editable: inputEditable,
    accessibilityIndex: accessibilityIndex,
    value: inputValue,
    onKeyDown: onInputKeyDown,
    onMouseDown: onInputMouseDown,
    onChange: onInputChange,
    onPaste: onInputPaste,
    tabIndex: tabIndex
  })), !combobox && item && !hasTextInput && _react.default.createElement("span", {
    className: "".concat(prefixCls, "-selection-item")
  }, item.label), !item && !hasTextInput && _react.default.createElement("span", {
    className: "".concat(prefixCls, "-selection-placeholder")
  }, placeholder));
};

var _default = SingleSelector;
exports.default = _default;