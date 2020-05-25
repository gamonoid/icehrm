import * as React from 'react';
import Header from '../Header';
import PanelContext from '../../PanelContext';

function MonthHeader(props) {
  var prefixCls = props.prefixCls,
      generateConfig = props.generateConfig,
      locale = props.locale,
      viewDate = props.viewDate,
      onNextYear = props.onNextYear,
      onPrevYear = props.onPrevYear,
      onYearClick = props.onYearClick;

  var _React$useContext = React.useContext(PanelContext),
      hideHeader = _React$useContext.hideHeader;

  if (hideHeader) {
    return null;
  }

  var headerPrefixCls = "".concat(prefixCls, "-header");
  return React.createElement(Header, {
    prefixCls: headerPrefixCls,
    onSuperPrev: onPrevYear,
    onSuperNext: onNextYear
  }, React.createElement("button", {
    type: "button",
    key: "year",
    onClick: onYearClick,
    className: "".concat(prefixCls, "-year-btn")
  }, generateConfig.locale.format(locale.locale, viewDate, locale.yearFormat)));
}

export default MonthHeader;