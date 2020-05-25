import * as React from 'react';
import Header from '../Header';
import PanelContext from '../../PanelContext';

function TimeHeader(props) {
  var _React$useContext = React.useContext(PanelContext),
      hideHeader = _React$useContext.hideHeader;

  if (hideHeader) {
    return null;
  }

  var prefixCls = props.prefixCls,
      generateConfig = props.generateConfig,
      locale = props.locale,
      value = props.value,
      format = props.format;
  var headerPrefixCls = "".concat(prefixCls, "-header");
  return React.createElement(Header, {
    prefixCls: headerPrefixCls
  }, value ? generateConfig.locale.format(locale.locale, value, format) : "\xA0");
}

export default TimeHeader;