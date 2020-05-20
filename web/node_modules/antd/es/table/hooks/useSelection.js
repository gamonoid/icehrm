function _toArray(arr) { return _arrayWithHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableRest(); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

import * as React from 'react';
import DownOutlined from '@ant-design/icons/DownOutlined';
import Checkbox from '../../checkbox';
import Dropdown from '../../dropdown';
import Menu from '../../menu';
import Radio from '../../radio';
import warning from '../../_util/warning';
var EMPTY_LIST = []; // TODO: warning if use ajax!!!

export var SELECTION_ALL = 'SELECT_ALL';
export var SELECTION_INVERT = 'SELECT_INVERT';

function getFixedType(column) {
  return column && column.fixed;
}

function flattenData(data, childrenColumnName) {
  var list = [];
  (data || []).forEach(function (record) {
    list.push(record);

    if (childrenColumnName in record) {
      list = [].concat(_toConsumableArray(list), _toConsumableArray(flattenData(record[childrenColumnName], childrenColumnName)));
    }
  });
  return list;
}

export default function useSelection(rowSelection, config) {
  var _ref = rowSelection || {},
      selectedRowKeys = _ref.selectedRowKeys,
      getCheckboxProps = _ref.getCheckboxProps,
      onSelectionChange = _ref.onChange,
      onSelect = _ref.onSelect,
      onSelectAll = _ref.onSelectAll,
      onSelectInvert = _ref.onSelectInvert,
      onSelectMultiple = _ref.onSelectMultiple,
      _ref$columnWidth = _ref.columnWidth,
      selectionColWidth = _ref$columnWidth === void 0 ? 60 : _ref$columnWidth,
      selectionType = _ref.type,
      selections = _ref.selections,
      fixed = _ref.fixed,
      customizeRenderCell = _ref.renderCell;

  var prefixCls = config.prefixCls,
      data = config.data,
      pageData = config.pageData,
      getRecordByKey = config.getRecordByKey,
      getRowKey = config.getRowKey,
      expandType = config.expandType,
      childrenColumnName = config.childrenColumnName,
      tableLocale = config.locale,
      expandIconColumnIndex = config.expandIconColumnIndex,
      getPopupContainer = config.getPopupContainer;

  var _React$useState = React.useState(),
      _React$useState2 = _slicedToArray(_React$useState, 2),
      innerSelectedKeys = _React$useState2[0],
      setInnerSelectedKeys = _React$useState2[1];

  var mergedSelectedKeys = selectedRowKeys || innerSelectedKeys || EMPTY_LIST;
  var mergedSelectedKeySet = React.useMemo(function () {
    var keys = selectionType === 'radio' ? mergedSelectedKeys.slice(0, 1) : mergedSelectedKeys;
    return new Set(keys);
  }, [mergedSelectedKeys, selectionType]); // Save last selected key to enable range selection

  var _React$useState3 = React.useState(null),
      _React$useState4 = _slicedToArray(_React$useState3, 2),
      lastSelectedKey = _React$useState4[0],
      setLastSelectedKey = _React$useState4[1]; // Reset if rowSelection reset


  React.useEffect(function () {
    if (!rowSelection) {
      setInnerSelectedKeys([]);
    }
  }, [!!rowSelection]);
  var setSelectedKeys = React.useCallback(function (keys) {
    setInnerSelectedKeys(keys);
    var records = keys.map(function (key) {
      return getRecordByKey(key);
    });

    if (onSelectionChange) {
      onSelectionChange(keys, records);
    }
  }, [setInnerSelectedKeys, getRecordByKey, onSelectionChange]); // Trigger single `onSelect` event

  var triggerSingleSelection = React.useCallback(function (key, selected, keys, event) {
    if (onSelect) {
      var rows = keys.map(function (k) {
        return getRecordByKey(k);
      });
      onSelect(getRecordByKey(key), selected, rows, event);
    }

    setSelectedKeys(keys);
  }, [onSelect, getRecordByKey, setSelectedKeys]);
  var mergedSelections = React.useMemo(function () {
    if (!selections) {
      return null;
    }

    var selectionList = selections === true ? [SELECTION_ALL, SELECTION_INVERT] : selections;
    return selectionList.map(function (selection) {
      if (selection === SELECTION_ALL) {
        return {
          key: 'all',
          text: tableLocale.selectionAll,
          onSelect: function onSelect() {
            setSelectedKeys(data.map(function (record, index) {
              return getRowKey(record, index);
            }));
          }
        };
      }

      if (selection === SELECTION_INVERT) {
        return {
          key: 'invert',
          text: tableLocale.selectInvert,
          onSelect: function onSelect() {
            var keySet = new Set(mergedSelectedKeySet);
            pageData.forEach(function (record, index) {
              var key = getRowKey(record, index);

              if (keySet.has(key)) {
                keySet["delete"](key);
              } else {
                keySet.add(key);
              }
            });
            var keys = Array.from(keySet);
            setSelectedKeys(keys);

            if (onSelectInvert) {
              warning(false, 'Table', '`onSelectInvert` will be removed in future. Please use `onChange` instead.');
              onSelectInvert(keys);
            }
          }
        };
      }

      return selection;
    });
  }, [selections, mergedSelectedKeySet, pageData, getRowKey]);
  var transformColumns = React.useCallback(function (columns) {
    if (!rowSelection) {
      return columns;
    } // Get flatten data


    var flattedData = flattenData(pageData, childrenColumnName); // Support selection

    var keySet = new Set(mergedSelectedKeySet); // Get all checkbox props

    var checkboxPropsMap = new Map();
    flattedData.forEach(function (record, index) {
      var key = getRowKey(record, index);
      var checkboxProps = (getCheckboxProps ? getCheckboxProps(record) : null) || {};
      checkboxPropsMap.set(key, checkboxProps);

      if (process.env.NODE_ENV !== 'production' && ('checked' in checkboxProps || 'defaultChecked' in checkboxProps)) {
        warning(false, 'Table', 'Do not set `checked` or `defaultChecked` in `getCheckboxProps`. Please use `selectedRowKeys` instead.');
      }
    }); // Record key only need check with enabled

    var recordKeys = flattedData.map(getRowKey).filter(function (key) {
      return !checkboxPropsMap.get(key).disabled;
    });
    var checkedCurrentAll = recordKeys.every(function (key) {
      return keySet.has(key);
    });
    var checkedCurrentSome = recordKeys.some(function (key) {
      return keySet.has(key);
    });

    var onSelectAllChange = function onSelectAllChange() {
      var changeKeys = [];

      if (checkedCurrentAll) {
        recordKeys.forEach(function (key) {
          keySet["delete"](key);
          changeKeys.push(key);
        });
      } else {
        recordKeys.forEach(function (key) {
          keySet.add(key);
          changeKeys.push(key);
        });
      }

      var keys = Array.from(keySet);
      setSelectedKeys(keys);

      if (onSelectAll) {
        onSelectAll(!checkedCurrentAll, keys.map(function (k) {
          return getRecordByKey(k);
        }), changeKeys.map(function (k) {
          return getRecordByKey(k);
        }));
      }
    }; // ===================== Render =====================
    // Title Cell


    var title;

    if (selectionType !== 'radio') {
      var customizeSelections;

      if (mergedSelections) {
        var menu = /*#__PURE__*/React.createElement(Menu, {
          getPopupContainer: getPopupContainer
        }, mergedSelections.map(function (selection, index) {
          var key = selection.key,
              text = selection.text,
              onSelectionClick = selection.onSelect;
          return /*#__PURE__*/React.createElement(Menu.Item, {
            key: key || index,
            onClick: function onClick() {
              if (onSelectionClick) {
                onSelectionClick(recordKeys);
              }
            }
          }, text);
        }));
        customizeSelections = /*#__PURE__*/React.createElement("div", {
          className: "".concat(prefixCls, "-selection-extra")
        }, /*#__PURE__*/React.createElement(Dropdown, {
          overlay: menu,
          getPopupContainer: getPopupContainer
        }, /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement(DownOutlined, null))));
      }

      var allDisabled = flattedData.every(function (record, index) {
        var key = getRowKey(record, index);
        var checkboxProps = checkboxPropsMap.get(key) || {};
        return checkboxProps.disabled;
      });
      title = /*#__PURE__*/React.createElement("div", {
        className: "".concat(prefixCls, "-selection")
      }, /*#__PURE__*/React.createElement(Checkbox, {
        checked: !allDisabled && !!flattedData.length && checkedCurrentAll,
        indeterminate: !checkedCurrentAll && checkedCurrentSome,
        onChange: onSelectAllChange,
        disabled: flattedData.length === 0 || allDisabled
      }), customizeSelections);
    } // Body Cell


    var renderCell;

    if (selectionType === 'radio') {
      renderCell = function renderCell(_, record, index) {
        var key = getRowKey(record, index);
        var checked = keySet.has(key);
        return {
          node: /*#__PURE__*/React.createElement(Radio, _extends({}, checkboxPropsMap.get(key), {
            checked: checked,
            onClick: function onClick(e) {
              return e.stopPropagation();
            },
            onChange: function onChange(event) {
              if (!keySet.has(key)) {
                triggerSingleSelection(key, true, [key], event.nativeEvent);
              }
            }
          })),
          checked: checked
        };
      };
    } else {
      renderCell = function renderCell(_, record, index) {
        var key = getRowKey(record, index);
        var checked = keySet.has(key); // Record checked

        return {
          node: /*#__PURE__*/React.createElement(Checkbox, _extends({}, checkboxPropsMap.get(key), {
            checked: checked,
            onClick: function onClick(e) {
              return e.stopPropagation();
            },
            onChange: function onChange(_ref2) {
              var nativeEvent = _ref2.nativeEvent;
              var shiftKey = nativeEvent.shiftKey;
              var startIndex = -1;
              var endIndex = -1; // Get range of this

              if (shiftKey) {
                var pointKeys = new Set([lastSelectedKey, key]);
                recordKeys.some(function (recordKey, recordIndex) {
                  if (pointKeys.has(recordKey)) {
                    if (startIndex === -1) {
                      startIndex = recordIndex;
                    } else {
                      endIndex = recordIndex;
                      return true;
                    }
                  }

                  return false;
                });
              }

              if (endIndex !== -1 && startIndex !== endIndex) {
                // Batch update selections
                var rangeKeys = recordKeys.slice(startIndex, endIndex + 1);
                var changedKeys = [];

                if (checked) {
                  rangeKeys.forEach(function (recordKey) {
                    if (keySet.has(recordKey)) {
                      changedKeys.push(recordKey);
                      keySet["delete"](recordKey);
                    }
                  });
                } else {
                  rangeKeys.forEach(function (recordKey) {
                    if (!keySet.has(recordKey)) {
                      changedKeys.push(recordKey);
                      keySet.add(recordKey);
                    }
                  });
                }

                var keys = Array.from(keySet);
                setSelectedKeys(keys);

                if (onSelectMultiple) {
                  onSelectMultiple(!checked, keys.map(function (recordKey) {
                    return getRecordByKey(recordKey);
                  }), changedKeys.map(function (recordKey) {
                    return getRecordByKey(recordKey);
                  }));
                }
              } else {
                // Single record selected
                if (checked) {
                  keySet["delete"](key);
                } else {
                  keySet.add(key);
                }

                triggerSingleSelection(key, !checked, Array.from(keySet), nativeEvent);
              }

              setLastSelectedKey(key);
            }
          })),
          checked: checked
        };
      };
    }

    var renderSelectionCell = function renderSelectionCell(_, record, index) {
      var _renderCell = renderCell(_, record, index),
          node = _renderCell.node,
          checked = _renderCell.checked;

      if (customizeRenderCell) {
        return customizeRenderCell(checked, record, index, node);
      }

      return node;
    }; // Columns


    var selectionColumn = {
      width: selectionColWidth,
      className: "".concat(prefixCls, "-selection-column"),
      title: rowSelection.columnTitle || title,
      render: renderSelectionCell
    };

    if (expandType === 'row' && columns.length && !expandIconColumnIndex) {
      var _columns = _toArray(columns),
          expandColumn = _columns[0],
          restColumns = _columns.slice(1);

      var selectionFixed = fixed || getFixedType(restColumns[0]);

      if (selectionFixed) {
        expandColumn.fixed = selectionFixed;
      }

      return [expandColumn, _extends(_extends({}, selectionColumn), {
        fixed: selectionFixed
      })].concat(_toConsumableArray(restColumns));
    }

    return [_extends(_extends({}, selectionColumn), {
      fixed: fixed || getFixedType(columns[0])
    })].concat(_toConsumableArray(columns));
  }, [getRowKey, pageData, rowSelection, innerSelectedKeys, mergedSelectedKeys, selectionColWidth, mergedSelections, expandType, lastSelectedKey, onSelectMultiple, triggerSingleSelection]);
  return [transformColumns, mergedSelectedKeySet];
}