"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var React = _interopRequireWildcard(require("react"));

var _Cell = _interopRequireDefault(require("./Cell"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function renderCells(items, _ref, _ref2) {
  var colon = _ref.colon,
      prefixCls = _ref.prefixCls,
      bordered = _ref.bordered;
  var component = _ref2.component,
      type = _ref2.type,
      showLabel = _ref2.showLabel,
      showContent = _ref2.showContent;
  return items.map(function (_ref3, index) {
    var _ref3$props = _ref3.props,
        label = _ref3$props.label,
        children = _ref3$props.children,
        _ref3$props$prefixCls = _ref3$props.prefixCls,
        itemPrefixCls = _ref3$props$prefixCls === void 0 ? prefixCls : _ref3$props$prefixCls,
        className = _ref3$props.className,
        style = _ref3$props.style,
        _ref3$props$span = _ref3$props.span,
        span = _ref3$props$span === void 0 ? 1 : _ref3$props$span,
        key = _ref3.key;

    if (typeof component === 'string') {
      return /*#__PURE__*/React.createElement(_Cell["default"], {
        key: "".concat(type, "-").concat(key || index),
        className: className,
        style: style,
        span: span,
        colon: colon,
        component: component,
        itemPrefixCls: itemPrefixCls,
        bordered: bordered,
        label: showLabel ? label : null,
        content: showContent ? children : null
      });
    }

    return [/*#__PURE__*/React.createElement(_Cell["default"], {
      key: "label-".concat(key || index),
      className: className,
      style: style,
      span: 1,
      colon: colon,
      component: component[0],
      itemPrefixCls: itemPrefixCls,
      bordered: bordered,
      label: label
    }), /*#__PURE__*/React.createElement(_Cell["default"], {
      key: "content-".concat(key || index),
      className: className,
      style: style,
      span: span * 2 - 1,
      component: component[1],
      itemPrefixCls: itemPrefixCls,
      bordered: bordered,
      content: children
    })];
  });
}

var Row = function Row(props) {
  var prefixCls = props.prefixCls,
      vertical = props.vertical,
      row = props.row,
      index = props.index,
      bordered = props.bordered;

  if (vertical) {
    return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("tr", {
      key: "label-".concat(index),
      className: "".concat(prefixCls, "-row")
    }, renderCells(row, props, {
      component: 'th',
      type: 'label',
      showLabel: true
    })), /*#__PURE__*/React.createElement("tr", {
      key: "content-".concat(index),
      className: "".concat(prefixCls, "-row")
    }, renderCells(row, props, {
      component: 'td',
      type: 'content',
      showContent: true
    })));
  }

  return /*#__PURE__*/React.createElement("tr", {
    key: index,
    className: "".concat(prefixCls, "-row")
  }, renderCells(row, props, {
    component: bordered ? ['th', 'td'] : 'td',
    type: 'item',
    showLabel: true,
    showContent: true
  }));
};

var _default = Row;
exports["default"] = _default;