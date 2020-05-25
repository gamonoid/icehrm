import * as React from 'react';
import { isSameQuarter } from '../../utils/dateUtil';
import RangeContext from '../../RangeContext';
import useCellClassName from '../../hooks/useCellClassName';
import PanelBody from '../PanelBody';
export var QUARTER_COL_COUNT = 4;
var QUARTER_ROW_COUNT = 1;

function QuarterBody(props) {
  var prefixCls = props.prefixCls,
      locale = props.locale,
      value = props.value,
      viewDate = props.viewDate,
      generateConfig = props.generateConfig;

  var _React$useContext = React.useContext(RangeContext),
      rangedValue = _React$useContext.rangedValue,
      hoverRangedValue = _React$useContext.hoverRangedValue;

  var cellPrefixCls = "".concat(prefixCls, "-cell");
  var getCellClassName = useCellClassName({
    cellPrefixCls: cellPrefixCls,
    value: value,
    generateConfig: generateConfig,
    rangedValue: rangedValue,
    hoverRangedValue: hoverRangedValue,
    isSameCell: function isSameCell(current, target) {
      return isSameQuarter(generateConfig, current, target);
    },
    isInView: function isInView() {
      return true;
    },
    offsetCell: function offsetCell(date, offset) {
      return generateConfig.addMonth(date, offset * 3);
    }
  });
  var baseQuarter = generateConfig.setDate(generateConfig.setMonth(viewDate, 0), 1);
  return React.createElement(PanelBody, Object.assign({}, props, {
    rowNum: QUARTER_ROW_COUNT,
    colNum: QUARTER_COL_COUNT,
    baseDate: baseQuarter,
    getCellText: function getCellText(date) {
      return generateConfig.locale.format(locale.locale, date, locale.quarterFormat || '[Q]Q');
    },
    getCellClassName: getCellClassName,
    getCellDate: function getCellDate(date, offset) {
      return generateConfig.addMonth(date, offset * 3);
    },
    titleCell: function titleCell(date) {
      return generateConfig.locale.format(locale.locale, date, 'YYYY-[Q]Q');
    }
  }));
}

export default QuarterBody;