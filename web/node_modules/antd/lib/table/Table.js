"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var React = _interopRequireWildcard(require("react"));

var _classnames = _interopRequireDefault(require("classnames"));

var _omit = _interopRequireDefault(require("omit.js"));

var _rcTable = _interopRequireDefault(require("rc-table"));

var _Table = require("rc-table/lib/Table");

var _spin = _interopRequireDefault(require("../spin"));

var _pagination = _interopRequireDefault(require("../pagination"));

var _context = require("../config-provider/context");

var _usePagination3 = _interopRequireWildcard(require("./hooks/usePagination"));

var _useLazyKVMap3 = _interopRequireDefault(require("./hooks/useLazyKVMap"));

var _useSelection3 = _interopRequireWildcard(require("./hooks/useSelection"));

var _useSorter3 = _interopRequireWildcard(require("./hooks/useSorter"));

var _useFilter3 = _interopRequireWildcard(require("./hooks/useFilter"));

var _useTitleColumns3 = _interopRequireDefault(require("./hooks/useTitleColumns"));

var _ExpandIcon = _interopRequireDefault(require("./ExpandIcon"));

var _scrollTo = _interopRequireDefault(require("../_util/scrollTo"));

var _en_US = _interopRequireDefault(require("../locale/en_US"));

var _SizeContext = _interopRequireDefault(require("../config-provider/SizeContext"));

var _Column = _interopRequireDefault(require("./Column"));

var _ColumnGroup = _interopRequireDefault(require("./ColumnGroup"));

var _warning = _interopRequireDefault(require("../_util/warning"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

var EMPTY_LIST = [];

function Table(props) {
  var _classNames3;

  var customizePrefixCls = props.prefixCls,
      className = props.className,
      style = props.style,
      customizeSize = props.size,
      bordered = props.bordered,
      customizeDropdownPrefixCls = props.dropdownPrefixCls,
      dataSource = props.dataSource,
      pagination = props.pagination,
      rowSelection = props.rowSelection,
      rowKey = props.rowKey,
      rowClassName = props.rowClassName,
      columns = props.columns,
      children = props.children,
      legacyChildrenColumnName = props.childrenColumnName,
      onChange = props.onChange,
      getPopupContainer = props.getPopupContainer,
      loading = props.loading,
      expandIcon = props.expandIcon,
      expandable = props.expandable,
      expandedRowRender = props.expandedRowRender,
      expandIconColumnIndex = props.expandIconColumnIndex,
      indentSize = props.indentSize,
      scroll = props.scroll,
      sortDirections = props.sortDirections,
      locale = props.locale,
      _props$showSorterTool = props.showSorterTooltip,
      showSorterTooltip = _props$showSorterTool === void 0 ? true : _props$showSorterTool;
  var tableProps = (0, _omit["default"])(props, ['className', 'style']);
  var size = React.useContext(_SizeContext["default"]);

  var _React$useContext = React.useContext(_context.ConfigContext),
      _React$useContext$loc = _React$useContext.locale,
      contextLocale = _React$useContext$loc === void 0 ? _en_US["default"] : _React$useContext$loc,
      renderEmpty = _React$useContext.renderEmpty,
      direction = _React$useContext.direction;

  var mergedSize = customizeSize || size;

  var tableLocale = _extends(_extends({}, contextLocale.Table), locale);

  var rawData = dataSource || EMPTY_LIST;

  var _React$useContext2 = React.useContext(_context.ConfigContext),
      getPrefixCls = _React$useContext2.getPrefixCls;

  var prefixCls = getPrefixCls('table', customizePrefixCls);
  var dropdownPrefixCls = getPrefixCls('dropdown', customizeDropdownPrefixCls);

  var mergedExpandable = _extends({
    childrenColumnName: legacyChildrenColumnName,
    expandIconColumnIndex: expandIconColumnIndex
  }, expandable);

  var _mergedExpandable$chi = mergedExpandable.childrenColumnName,
      childrenColumnName = _mergedExpandable$chi === void 0 ? 'children' : _mergedExpandable$chi;
  var expandType = React.useMemo(function () {
    if (rawData.some(function (item) {
      return item[childrenColumnName];
    })) {
      return 'nest';
    }

    if (expandedRowRender || expandable && expandable.expandedRowRender) {
      return 'row';
    }

    return null;
  }, [rawData]);
  var internalRefs = {
    body: React.useRef()
  }; // ============================ RowKey ============================

  var getRowKey = React.useMemo(function () {
    if (typeof rowKey === 'function') {
      return rowKey;
    }

    return function (record) {
      return record[rowKey];
    };
  }, [rowKey]);

  var _useLazyKVMap = (0, _useLazyKVMap3["default"])(rawData, childrenColumnName, getRowKey),
      _useLazyKVMap2 = _slicedToArray(_useLazyKVMap, 1),
      getRecordByKey = _useLazyKVMap2[0]; // ============================ Events =============================


  var changeEventInfo = {};

  var triggerOnChange = function triggerOnChange(info) {
    var reset = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

    var changeInfo = _extends(_extends({}, changeEventInfo), info);

    if (reset) {
      changeEventInfo.resetPagination(); // Reset event param

      if (changeInfo.pagination.current) {
        changeInfo.pagination.current = 1;
      } // Trigger pagination events


      if (pagination && pagination.onChange) {
        pagination.onChange(1, changeInfo.pagination.pageSize);
      }
    }

    if (scroll && scroll.scrollToFirstRowOnChange !== false && internalRefs.body.current) {
      (0, _scrollTo["default"])(0, {
        getContainer: function getContainer() {
          return internalRefs.body.current;
        }
      });
    }

    if (onChange) {
      onChange(changeInfo.pagination, changeInfo.filters, changeInfo.sorter, {
        currentDataSource: (0, _useFilter3.getFilterData)((0, _useSorter3.getSortData)(rawData, changeInfo.sorterStates, childrenColumnName), changeInfo.filterStates)
      });
    }
  };
  /**
   * Controlled state in `columns` is not a good idea that makes too many code (1000+ line?)
   * to read state out and then put it back to title render.
   * Move these code into `hooks` but still too complex.
   * We should provides Table props like `sorter` & `filter` to handle control in next big version.
   */
  // ============================ Sorter =============================


  var onSorterChange = function onSorterChange(sorter, sorterStates) {
    triggerOnChange({
      sorter: sorter,
      sorterStates: sorterStates
    }, false);
  };

  var _useSorter = (0, _useSorter3["default"])({
    prefixCls: prefixCls,
    columns: columns,
    children: children,
    onSorterChange: onSorterChange,
    sortDirections: sortDirections || ['ascend', 'descend'],
    tableLocale: tableLocale,
    showSorterTooltip: showSorterTooltip
  }),
      _useSorter2 = _slicedToArray(_useSorter, 4),
      transformSorterColumns = _useSorter2[0],
      sortStates = _useSorter2[1],
      sorterTitleProps = _useSorter2[2],
      getSorters = _useSorter2[3];

  var sortedData = React.useMemo(function () {
    return (0, _useSorter3.getSortData)(rawData, sortStates, childrenColumnName);
  }, [rawData, sortStates]);
  changeEventInfo.sorter = getSorters();
  changeEventInfo.sorterStates = sortStates; // ============================ Filter ============================

  var onFilterChange = function onFilterChange(filters, filterStates) {
    triggerOnChange({
      filters: filters,
      filterStates: filterStates
    }, true);
  };

  var _useFilter = (0, _useFilter3["default"])({
    prefixCls: prefixCls,
    locale: tableLocale,
    dropdownPrefixCls: dropdownPrefixCls,
    columns: columns,
    children: children,
    onFilterChange: onFilterChange,
    getPopupContainer: getPopupContainer
  }),
      _useFilter2 = _slicedToArray(_useFilter, 3),
      transformFilterColumns = _useFilter2[0],
      filterStates = _useFilter2[1],
      getFilters = _useFilter2[2];

  var mergedData = (0, _useFilter3.getFilterData)(sortedData, filterStates);
  changeEventInfo.filters = getFilters();
  changeEventInfo.filterStates = filterStates; // ============================ Column ============================

  var columnTitleProps = React.useMemo(function () {
    return _extends({}, sorterTitleProps);
  }, [sorterTitleProps]);

  var _useTitleColumns = (0, _useTitleColumns3["default"])(columnTitleProps),
      _useTitleColumns2 = _slicedToArray(_useTitleColumns, 1),
      transformTitleColumns = _useTitleColumns2[0]; // ========================== Pagination ==========================


  var onPaginationChange = function onPaginationChange(current, pageSize) {
    triggerOnChange({
      pagination: _extends(_extends({}, changeEventInfo.pagination), {
        current: current,
        pageSize: pageSize
      })
    });
  };

  var _usePagination = (0, _usePagination3["default"])(mergedData.length, pagination, onPaginationChange),
      _usePagination2 = _slicedToArray(_usePagination, 2),
      mergedPagination = _usePagination2[0],
      resetPagination = _usePagination2[1];

  changeEventInfo.pagination = pagination === false ? {} : (0, _usePagination3.getPaginationParam)(pagination, mergedPagination);
  changeEventInfo.resetPagination = resetPagination; // ============================= Data =============================

  var pageData = React.useMemo(function () {
    if (pagination === false || !mergedPagination.pageSize) {
      return mergedData;
    }

    var _mergedPagination$cur = mergedPagination.current,
        current = _mergedPagination$cur === void 0 ? 1 : _mergedPagination$cur,
        total = mergedPagination.total,
        _mergedPagination$pag = mergedPagination.pageSize,
        pageSize = _mergedPagination$pag === void 0 ? _usePagination3.DEFAULT_PAGE_SIZE : _mergedPagination$pag; // Dynamic table data

    if (mergedData.length < total) {
      if (mergedData.length > pageSize) {
        (0, _warning["default"])(false, 'Table', '`dataSource` length is less than `pagination.total` but large than `pagination.pageSize`. Please make sure your config correct data with async mode.');
        return mergedData.slice((current - 1) * pageSize, current * pageSize);
      }

      return mergedData;
    }

    var currentPageData = mergedData.slice((current - 1) * pageSize, current * pageSize);
    return currentPageData;
  }, [!!pagination, mergedData, mergedPagination && mergedPagination.current, mergedPagination && mergedPagination.pageSize, mergedPagination && mergedPagination.total]); // ========================== Selections ==========================

  var _useSelection = (0, _useSelection3["default"])(rowSelection, {
    prefixCls: prefixCls,
    data: mergedData,
    pageData: pageData,
    getRowKey: getRowKey,
    getRecordByKey: getRecordByKey,
    expandType: expandType,
    childrenColumnName: childrenColumnName,
    locale: tableLocale,
    expandIconColumnIndex: mergedExpandable.expandIconColumnIndex,
    getPopupContainer: getPopupContainer
  }),
      _useSelection2 = _slicedToArray(_useSelection, 2),
      transformSelectionColumns = _useSelection2[0],
      selectedKeySet = _useSelection2[1];

  var internalRowClassName = function internalRowClassName(record, index, indent) {
    var mergedRowClassName;

    if (typeof rowClassName === 'function') {
      mergedRowClassName = (0, _classnames["default"])(rowClassName(record, index, indent));
    } else {
      mergedRowClassName = (0, _classnames["default"])(rowClassName);
    }

    return (0, _classnames["default"])(_defineProperty({}, "".concat(prefixCls, "-row-selected"), selectedKeySet.has(getRowKey(record, index))), mergedRowClassName);
  }; // ========================== Expandable ==========================
  // Pass origin render status into `rc-table`, this can be removed when refactor with `rc-table`


  mergedExpandable.__PARENT_RENDER_ICON__ = mergedExpandable.expandIcon; // Customize expandable icon

  mergedExpandable.expandIcon = mergedExpandable.expandIcon || expandIcon || (0, _ExpandIcon["default"])(tableLocale); // Adjust expand icon index, no overwrite expandIconColumnIndex if set.

  if (expandType === 'nest' && mergedExpandable.expandIconColumnIndex === undefined) {
    mergedExpandable.expandIconColumnIndex = rowSelection ? 1 : 0;
  } else if (mergedExpandable.expandIconColumnIndex > 0 && rowSelection) {
    mergedExpandable.expandIconColumnIndex -= 1;
  } // Indent size


  mergedExpandable.indentSize = mergedExpandable.indentSize || indentSize || 15; // ============================ Render ============================

  var transformColumns = React.useCallback(function (innerColumns) {
    return transformTitleColumns(transformSelectionColumns(transformFilterColumns(transformSorterColumns(innerColumns))));
  }, [transformSorterColumns, transformFilterColumns, transformSelectionColumns]);
  var topPaginationNode;
  var bottomPaginationNode;

  if (pagination !== false) {
    var paginationSize;

    if (mergedPagination.size) {
      paginationSize = mergedPagination.size;
    } else {
      paginationSize = mergedSize === 'small' || mergedSize === 'middle' ? 'small' : undefined;
    }

    var renderPagination = function renderPagination() {
      var position = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'right';
      return /*#__PURE__*/React.createElement(_pagination["default"], _extends({
        className: "".concat(prefixCls, "-pagination ").concat(prefixCls, "-pagination-").concat(position)
      }, mergedPagination, {
        size: paginationSize
      }));
    };

    if (mergedPagination.position !== null && Array.isArray(mergedPagination.position)) {
      var topPos = mergedPagination.position.find(function (p) {
        return p.indexOf('top') !== -1;
      });
      var bottomPos = mergedPagination.position.find(function (p) {
        return p.indexOf('bottom') !== -1;
      });

      if (!topPos && !bottomPos) {
        bottomPaginationNode = renderPagination();
      } else {
        if (topPos) {
          topPaginationNode = renderPagination(topPos.toLowerCase().replace('top', ''));
        }

        if (bottomPos) {
          bottomPaginationNode = renderPagination(bottomPos.toLowerCase().replace('bottom', ''));
        }
      }
    } else {
      bottomPaginationNode = renderPagination();
    }
  } // >>>>>>>>> Spinning


  var spinProps;

  if (typeof loading === 'boolean') {
    spinProps = {
      spinning: loading
    };
  } else if (_typeof(loading) === 'object') {
    spinProps = _extends({
      spinning: true
    }, loading);
  }

  var wrapperClassNames = (0, _classnames["default"])("".concat(prefixCls, "-wrapper"), className, _defineProperty({}, "".concat(prefixCls, "-wrapper-rtl"), direction === 'rtl'));
  return /*#__PURE__*/React.createElement("div", {
    className: wrapperClassNames,
    style: style
  }, /*#__PURE__*/React.createElement(_spin["default"], _extends({
    spinning: false
  }, spinProps), topPaginationNode, /*#__PURE__*/React.createElement(_rcTable["default"], _extends({}, tableProps, {
    direction: direction,
    expandable: mergedExpandable,
    prefixCls: prefixCls,
    className: (0, _classnames["default"])((_classNames3 = {}, _defineProperty(_classNames3, "".concat(prefixCls, "-middle"), mergedSize === 'middle'), _defineProperty(_classNames3, "".concat(prefixCls, "-small"), mergedSize === 'small'), _defineProperty(_classNames3, "".concat(prefixCls, "-bordered"), bordered), _classNames3)),
    data: pageData,
    rowKey: getRowKey,
    rowClassName: internalRowClassName,
    emptyText: locale && locale.emptyText || renderEmpty('Table') // Internal
    ,
    internalHooks: _Table.INTERNAL_HOOKS,
    internalRefs: internalRefs,
    transformColumns: transformColumns
  })), pageData && pageData.length > 0 && bottomPaginationNode));
}

Table.defaultProps = {
  rowKey: 'key'
};
Table.SELECTION_ALL = _useSelection3.SELECTION_ALL;
Table.SELECTION_INVERT = _useSelection3.SELECTION_INVERT;
Table.Column = _Column["default"];
Table.ColumnGroup = _ColumnGroup["default"];
var _default = Table;
exports["default"] = _default;