"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = renderSwitcherIcon;

var _react = _interopRequireDefault(require("react"));

var _classnames = _interopRequireDefault(require("classnames"));

var _LoadingOutlined = _interopRequireDefault(require("@ant-design/icons/LoadingOutlined"));

var _FileOutlined = _interopRequireDefault(require("@ant-design/icons/FileOutlined"));

var _MinusSquareOutlined = _interopRequireDefault(require("@ant-design/icons/MinusSquareOutlined"));

var _PlusSquareOutlined = _interopRequireDefault(require("@ant-design/icons/PlusSquareOutlined"));

var _CaretDownFilled = _interopRequireDefault(require("@ant-design/icons/CaretDownFilled"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function renderSwitcherIcon(prefixCls, switcherIcon, showLine, _ref) {
  var isLeaf = _ref.isLeaf,
      expanded = _ref.expanded,
      loading = _ref.loading;

  if (loading) {
    return /*#__PURE__*/_react["default"].createElement(_LoadingOutlined["default"], {
      className: "".concat(prefixCls, "-switcher-loading-icon")
    });
  }

  if (isLeaf) {
    return showLine ? /*#__PURE__*/_react["default"].createElement(_FileOutlined["default"], {
      className: "".concat(prefixCls, "-switcher-line-icon")
    }) : null;
  }

  var switcherCls = "".concat(prefixCls, "-switcher-icon");

  if (_react["default"].isValidElement(switcherIcon)) {
    return _react["default"].cloneElement(switcherIcon, {
      className: (0, _classnames["default"])(switcherIcon.props.className || '', switcherCls)
    });
  }

  if (switcherIcon) {
    return switcherIcon;
  }

  if (showLine) {
    return expanded ? /*#__PURE__*/_react["default"].createElement(_MinusSquareOutlined["default"], {
      className: "".concat(prefixCls, "-switcher-line-icon")
    }) : /*#__PURE__*/_react["default"].createElement(_PlusSquareOutlined["default"], {
      className: "".concat(prefixCls, "-switcher-line-icon")
    });
  }

  return /*#__PURE__*/_react["default"].createElement(_CaretDownFilled["default"], {
    className: switcherCls
  });
}