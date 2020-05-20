function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

import * as React from 'react';
import warning from "rc-util/es/warning";
import toArray from "rc-util/es/Children/toArray";
import { INTERNAL_COL_DEFINE } from '../utils/legacyUtil';
export function convertChildrenToColumns(children) {
  return toArray(children).filter(function (node) {
    return React.isValidElement(node);
  }).map(function (_ref) {
    var key = _ref.key,
        props = _ref.props;

    var nodeChildren = props.children,
        restProps = _objectWithoutProperties(props, ["children"]);

    var column = _objectSpread({
      key: key
    }, restProps);

    if (nodeChildren) {
      column.children = convertChildrenToColumns(nodeChildren);
    }

    return column;
  });
}

function flatColumns(columns) {
  return columns.reduce(function (list, column) {
    var fixed = column.fixed; // Convert `fixed='true'` to `fixed='left'` instead

    var parsedFixed = fixed === true ? 'left' : fixed;
    var subColumns = column.children;

    if (subColumns && subColumns.length > 0) {
      return [].concat(_toConsumableArray(list), _toConsumableArray(flatColumns(subColumns).map(function (subColum) {
        return _objectSpread({
          fixed: parsedFixed
        }, subColum);
      })));
    }

    return [].concat(_toConsumableArray(list), [_objectSpread({}, column, {
      fixed: parsedFixed
    })]);
  }, []);
}

function warningFixed(flattenColumns) {
  var allFixLeft = true;

  for (var i = 0; i < flattenColumns.length; i += 1) {
    var col = flattenColumns[i];

    if (allFixLeft && col.fixed !== 'left') {
      allFixLeft = false;
    } else if (!allFixLeft && col.fixed === 'left') {
      warning(false, "Index ".concat(i - 1, " of `columns` missing `fixed='left'` prop."));
      break;
    }
  }

  var allFixRight = true;

  for (var _i = flattenColumns.length - 1; _i >= 0; _i -= 1) {
    var _col = flattenColumns[_i];

    if (allFixRight && _col.fixed !== 'right') {
      allFixRight = false;
    } else if (!allFixRight && _col.fixed === 'right') {
      warning(false, "Index ".concat(_i + 1, " of `columns` missing `fixed='right'` prop."));
      break;
    }
  }
}

function revertForRtl(columns) {
  return columns.map(function (column) {
    var fixed = column.fixed,
        restProps = _objectWithoutProperties(column, ["fixed"]); // Convert `fixed='left'` to `fixed='right'` instead


    var parsedFixed = fixed;

    if (fixed === 'left') {
      parsedFixed = 'right';
    } else if (fixed === 'right') {
      parsedFixed = 'left';
    }

    return _objectSpread({
      fixed: parsedFixed
    }, restProps);
  });
}
/**
 * Parse `columns` & `children` into `columns`.
 */


function useColumns(_ref2, transformColumns) {
  var prefixCls = _ref2.prefixCls,
      columns = _ref2.columns,
      children = _ref2.children,
      expandable = _ref2.expandable,
      expandedKeys = _ref2.expandedKeys,
      getRowKey = _ref2.getRowKey,
      onTriggerExpand = _ref2.onTriggerExpand,
      expandIcon = _ref2.expandIcon,
      rowExpandable = _ref2.rowExpandable,
      expandIconColumnIndex = _ref2.expandIconColumnIndex,
      direction = _ref2.direction;
  var baseColumns = React.useMemo(function () {
    return columns || convertChildrenToColumns(children);
  }, [columns, children]); // Add expand column

  var withExpandColumns = React.useMemo(function () {
    if (expandable) {
      var _expandColumn;

      var expandColIndex = expandIconColumnIndex || 0;
      var prevColumn = baseColumns[expandColIndex];
      var expandColumn = (_expandColumn = {}, _defineProperty(_expandColumn, INTERNAL_COL_DEFINE, {
        className: "".concat(prefixCls, "-expand-icon-col")
      }), _defineProperty(_expandColumn, "title", ''), _defineProperty(_expandColumn, "fixed", prevColumn ? prevColumn.fixed : null), _defineProperty(_expandColumn, "className", "".concat(prefixCls, "-row-expand-icon-cell")), _defineProperty(_expandColumn, "render", function render(_, record, index) {
        var rowKey = getRowKey(record, index);
        var expanded = expandedKeys.has(rowKey);
        var recordExpandable = rowExpandable ? rowExpandable(record) : true;
        return expandIcon({
          prefixCls: prefixCls,
          expanded: expanded,
          expandable: recordExpandable,
          record: record,
          onExpand: onTriggerExpand
        });
      }), _expandColumn); // Insert expand column in the target position

      var cloneColumns = baseColumns.slice();
      cloneColumns.splice(expandColIndex, 0, expandColumn);
      return cloneColumns;
    }

    return baseColumns;
  }, [expandable, baseColumns, getRowKey, expandedKeys, expandIcon, direction]);
  var mergedColumns = React.useMemo(function () {
    var finalColumns = withExpandColumns;

    if (transformColumns) {
      finalColumns = transformColumns(finalColumns);
    } // Always provides at least one column for table display


    if (!finalColumns.length) {
      finalColumns = [{
        render: function render() {
          return null;
        }
      }];
    }

    return finalColumns;
  }, [transformColumns, withExpandColumns, direction]);
  var flattenColumns = React.useMemo(function () {
    if (direction === 'rtl') {
      return revertForRtl(flatColumns(mergedColumns));
    }

    return flatColumns(mergedColumns);
  }, [mergedColumns, direction]); // Only check out of production since it's waste for each render

  if (process.env.NODE_ENV !== 'production') {
    warningFixed(flattenColumns);
  }

  return [mergedColumns, flattenColumns];
}

export default useColumns;