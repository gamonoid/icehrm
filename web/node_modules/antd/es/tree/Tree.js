function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { return function () { var Super = _getPrototypeOf(Derived), result; if (_isNativeReflectConstruct()) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

import * as React from 'react';
import RcTree, { TreeNode } from 'rc-tree';
import classNames from 'classnames';
import DirectoryTree from './DirectoryTree';
import { ConfigConsumer } from '../config-provider';
import collapseMotion from '../_util/motion';
import renderSwitcherIcon from './utils/iconUtil';

var Tree = /*#__PURE__*/function (_React$Component) {
  _inherits(Tree, _React$Component);

  var _super = _createSuper(Tree);

  function Tree() {
    var _this;

    _classCallCheck(this, Tree);

    _this = _super.apply(this, arguments);

    _this.setTreeRef = function (node) {
      _this.tree = node;
    };

    _this.renderTree = function (_ref) {
      var _classNames;

      var getPrefixCls = _ref.getPrefixCls,
          direction = _ref.direction;

      var _assertThisInitialize = _assertThisInitialized(_this),
          props = _assertThisInitialize.props;

      var customizePrefixCls = props.prefixCls,
          className = props.className,
          showIcon = props.showIcon,
          showLine = props.showLine,
          _switcherIcon = props.switcherIcon,
          blockNode = props.blockNode,
          children = props.children;
      var checkable = props.checkable;
      var prefixCls = getPrefixCls('tree', customizePrefixCls);
      return /*#__PURE__*/React.createElement(RcTree, _extends({
        itemHeight: 20,
        ref: _this.setTreeRef
      }, props, {
        prefixCls: prefixCls,
        className: classNames(className, (_classNames = {}, _defineProperty(_classNames, "".concat(prefixCls, "-icon-hide"), !showIcon), _defineProperty(_classNames, "".concat(prefixCls, "-block-node"), blockNode), _defineProperty(_classNames, "".concat(prefixCls, "-rtl"), direction === 'rtl'), _classNames)),
        checkable: checkable ? /*#__PURE__*/React.createElement("span", {
          className: "".concat(prefixCls, "-checkbox-inner")
        }) : checkable,
        switcherIcon: function switcherIcon(nodeProps) {
          return renderSwitcherIcon(prefixCls, _switcherIcon, showLine, nodeProps);
        }
      }), children);
    };

    return _this;
  }

  _createClass(Tree, [{
    key: "render",
    value: function render() {
      return /*#__PURE__*/React.createElement(ConfigConsumer, null, this.renderTree);
    }
  }]);

  return Tree;
}(React.Component);

export { Tree as default };
Tree.TreeNode = TreeNode;
Tree.DirectoryTree = DirectoryTree;
Tree.defaultProps = {
  checkable: false,
  showIcon: false,
  motion: _extends(_extends({}, collapseMotion), {
    motionAppear: false
  }),
  blockNode: false
};