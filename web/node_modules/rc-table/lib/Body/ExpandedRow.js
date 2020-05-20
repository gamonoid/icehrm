"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var React = _interopRequireWildcard(require("react"));

var _Cell = _interopRequireDefault(require("../Cell"));

var _TableContext = _interopRequireDefault(require("../context/TableContext"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function ExpandedRow(_ref) {
  var prefixCls = _ref.prefixCls,
      children = _ref.children,
      Component = _ref.component,
      cellComponent = _ref.cellComponent,
      fixHeader = _ref.fixHeader,
      fixColumn = _ref.fixColumn,
      horizonScroll = _ref.horizonScroll,
      className = _ref.className,
      expanded = _ref.expanded,
      componentWidth = _ref.componentWidth,
      colSpan = _ref.colSpan;

  var _React$useContext = React.useContext(_TableContext.default),
      scrollbarSize = _React$useContext.scrollbarSize; // Cache render node


  return React.useMemo(function () {
    var contentNode = children;

    if (fixColumn) {
      contentNode = React.createElement("div", {
        style: {
          width: componentWidth - (fixHeader ? scrollbarSize : 0),
          position: 'sticky',
          left: 0,
          overflow: 'hidden'
        },
        className: "".concat(prefixCls, "-expanded-row-fixed")
      }, contentNode);
    }

    return React.createElement(Component, {
      className: className,
      style: {
        display: expanded ? null : 'none'
      }
    }, React.createElement(_Cell.default, {
      component: cellComponent,
      prefixCls: prefixCls,
      colSpan: colSpan
    }, contentNode));
  }, [children, Component, fixHeader, horizonScroll, className, expanded, componentWidth, colSpan, scrollbarSize]);
}

var _default = ExpandedRow;
exports.default = _default;