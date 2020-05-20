import * as React from 'react';
import ResizeObserver from 'rc-resize-observer';
import BodyRow from './BodyRow';
import TableContext from '../context/TableContext';
import ExpandedRow from './ExpandedRow';
import BodyContext from '../context/BodyContext';
import { getColumnsKey } from '../utils/valueUtil';
import ResizeContext from '../context/ResizeContext';

function Body(_ref) {
  var data = _ref.data,
      getRowKey = _ref.getRowKey,
      measureColumnWidth = _ref.measureColumnWidth,
      stickyOffsets = _ref.stickyOffsets,
      expandedKeys = _ref.expandedKeys,
      onRow = _ref.onRow,
      rowExpandable = _ref.rowExpandable,
      emptyNode = _ref.emptyNode,
      childrenColumnName = _ref.childrenColumnName;

  var _React$useContext = React.useContext(ResizeContext),
      onColumnResize = _React$useContext.onColumnResize;

  var _React$useContext2 = React.useContext(TableContext),
      prefixCls = _React$useContext2.prefixCls,
      getComponent = _React$useContext2.getComponent;

  var _React$useContext3 = React.useContext(BodyContext),
      fixHeader = _React$useContext3.fixHeader,
      horizonScroll = _React$useContext3.horizonScroll,
      flattenColumns = _React$useContext3.flattenColumns,
      componentWidth = _React$useContext3.componentWidth;

  return React.useMemo(function () {
    var WrapperComponent = getComponent(['body', 'wrapper'], 'tbody');
    var trComponent = getComponent(['body', 'row'], 'tr');
    var tdComponent = getComponent(['body', 'cell'], 'td');
    var rows;

    if (data.length) {
      rows = data.map(function (record, index) {
        var key = getRowKey(record, index);
        return [React.createElement(BodyRow, {
          key: key,
          rowKey: key,
          record: record,
          recordKey: key,
          index: index,
          rowComponent: trComponent,
          cellComponent: tdComponent,
          stickyOffsets: stickyOffsets,
          expandedKeys: expandedKeys,
          onRow: onRow,
          getRowKey: getRowKey,
          rowExpandable: rowExpandable,
          childrenColumnName: childrenColumnName
        })];
      });
    } else {
      rows = React.createElement(ExpandedRow, {
        expanded: true,
        className: "".concat(prefixCls, "-placeholder"),
        prefixCls: prefixCls,
        fixHeader: fixHeader,
        fixColumn: horizonScroll,
        horizonScroll: horizonScroll,
        component: trComponent,
        componentWidth: componentWidth,
        cellComponent: tdComponent,
        colSpan: flattenColumns.length
      }, emptyNode);
    }

    var columnsKey = getColumnsKey(flattenColumns);
    return React.createElement(WrapperComponent, {
      className: "".concat(prefixCls, "-tbody")
    }, measureColumnWidth && React.createElement("tr", {
      "aria-hidden": "true",
      className: "".concat(prefixCls, "-measure-row"),
      style: {
        height: 0
      }
    }, columnsKey.map(function (columnKey) {
      return React.createElement(ResizeObserver, {
        key: columnKey,
        onResize: function onResize(_ref2) {
          var offsetWidth = _ref2.offsetWidth;
          onColumnResize(columnKey, offsetWidth);
        }
      }, React.createElement("td", {
        style: {
          padding: 0,
          border: 0,
          height: 0
        }
      }));
    })), rows);
  }, [data, prefixCls, onRow, measureColumnWidth, stickyOffsets, expandedKeys, getRowKey, getComponent, componentWidth, emptyNode]);
}

var MemoBody = React.memo(Body);
MemoBody.displayName = 'Body';
export default MemoBody;