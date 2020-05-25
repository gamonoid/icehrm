import React from 'react';
import classNames from 'classnames';
import LoadingOutlined from '@ant-design/icons/LoadingOutlined';
import FileOutlined from '@ant-design/icons/FileOutlined';
import MinusSquareOutlined from '@ant-design/icons/MinusSquareOutlined';
import PlusSquareOutlined from '@ant-design/icons/PlusSquareOutlined';
import CaretDownFilled from '@ant-design/icons/CaretDownFilled';
export default function renderSwitcherIcon(prefixCls, switcherIcon, showLine, _ref) {
  var isLeaf = _ref.isLeaf,
      expanded = _ref.expanded,
      loading = _ref.loading;

  if (loading) {
    return /*#__PURE__*/React.createElement(LoadingOutlined, {
      className: "".concat(prefixCls, "-switcher-loading-icon")
    });
  }

  if (isLeaf) {
    return showLine ? /*#__PURE__*/React.createElement(FileOutlined, {
      className: "".concat(prefixCls, "-switcher-line-icon")
    }) : null;
  }

  var switcherCls = "".concat(prefixCls, "-switcher-icon");

  if (React.isValidElement(switcherIcon)) {
    return React.cloneElement(switcherIcon, {
      className: classNames(switcherIcon.props.className || '', switcherCls)
    });
  }

  if (switcherIcon) {
    return switcherIcon;
  }

  if (showLine) {
    return expanded ? /*#__PURE__*/React.createElement(MinusSquareOutlined, {
      className: "".concat(prefixCls, "-switcher-line-icon")
    }) : /*#__PURE__*/React.createElement(PlusSquareOutlined, {
      className: "".concat(prefixCls, "-switcher-line-icon")
    });
  }

  return /*#__PURE__*/React.createElement(CaretDownFilled, {
    className: switcherCls
  });
}