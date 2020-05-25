"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _react = _interopRequireWildcard(require("react"));

var _raf = _interopRequireDefault(require("raf"));

var _rcAlign = _interopRequireDefault(require("rc-align"));

var _ref5 = require("rc-util/lib/ref");

var _classnames = _interopRequireDefault(require("classnames"));

var _CSSMotion = _interopRequireDefault(require("rc-animate/lib/CSSMotion"));

var _PopupInner = _interopRequireDefault(require("./PopupInner"));

var _legacyUtil = require("./utils/legacyUtil");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; if (obj != null) { var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var CSSMotion = _CSSMotion.default;

function supportMotion(motion) {
  return motion && motion.motionName;
}

var Popup =
/*#__PURE__*/
function (_Component) {
  _inherits(Popup, _Component);

  function Popup() {
    var _this;

    _classCallCheck(this, Popup);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(Popup).apply(this, arguments));
    _this.state = {
      targetWidth: undefined,
      targetHeight: undefined,
      status: null,
      prevVisible: null,
      alignClassName: null
    };
    _this.popupRef = _react.default.createRef();
    _this.alignRef = _react.default.createRef();
    _this.nextFrameState = null;
    _this.nextFrameId = null;

    _this.onAlign = function (popupDomNode, align) {
      var status = _this.state.status;
      var _this$props = _this.props,
          getClassNameFromAlign = _this$props.getClassNameFromAlign,
          onAlign = _this$props.onAlign;
      var alignClassName = getClassNameFromAlign(align);

      if (status === 'align') {
        _this.setState({
          alignClassName: alignClassName,
          status: 'aligned'
        }, function () {
          _this.alignRef.current.forceAlign();
        });
      } else if (status === 'aligned') {
        _this.setState({
          alignClassName: alignClassName,
          status: 'afterAlign'
        });

        onAlign(popupDomNode, align);
      } else {
        _this.setState({
          alignClassName: alignClassName
        });
      }
    };

    _this.onMotionEnd = function () {
      var visible = _this.props.visible;

      _this.setState({
        status: visible ? 'AfterMotion' : 'stable'
      });
    };

    _this.setStateOnNextFrame = function (state) {
      _this.cancelFrameState();

      _this.nextFrameState = _objectSpread({}, _this.nextFrameState, {}, state);
      _this.nextFrameId = (0, _raf.default)(function () {
        var submitState = _objectSpread({}, _this.nextFrameState);

        _this.nextFrameState = null;

        _this.setState(submitState);
      });
    };

    _this.getMotion = function () {
      return _objectSpread({}, (0, _legacyUtil.getMotion)(_this.props));
    }; // `target` on `rc-align` can accept as a function to get the bind element or a point.
    // ref: https://www.npmjs.com/package/rc-align


    _this.getAlignTarget = function () {
      var _this$props2 = _this.props,
          point = _this$props2.point,
          getRootDomNode = _this$props2.getRootDomNode;

      if (point) {
        return point;
      }

      return getRootDomNode;
    };

    _this.cancelFrameState = function () {
      _raf.default.cancel(_this.nextFrameId);
    };

    _this.renderPopupElement = function () {
      var _this$state = _this.state,
          status = _this$state.status,
          targetHeight = _this$state.targetHeight,
          targetWidth = _this$state.targetWidth,
          alignClassName = _this$state.alignClassName;
      var _this$props3 = _this.props,
          prefixCls = _this$props3.prefixCls,
          className = _this$props3.className,
          style = _this$props3.style,
          stretch = _this$props3.stretch,
          visible = _this$props3.visible,
          align = _this$props3.align,
          destroyPopupOnHide = _this$props3.destroyPopupOnHide,
          onMouseEnter = _this$props3.onMouseEnter,
          onMouseLeave = _this$props3.onMouseLeave,
          onMouseDown = _this$props3.onMouseDown,
          onTouchStart = _this$props3.onTouchStart,
          children = _this$props3.children;
      var mergedClassName = (0, _classnames.default)(prefixCls, className, alignClassName);
      var hiddenClassName = "".concat(prefixCls, "-hidden"); // ================== Style ==================

      var sizeStyle = {};

      if (stretch) {
        // Stretch with target
        if (stretch.indexOf('height') !== -1) {
          sizeStyle.height = targetHeight;
        } else if (stretch.indexOf('minHeight') !== -1) {
          sizeStyle.minHeight = targetHeight;
        }

        if (stretch.indexOf('width') !== -1) {
          sizeStyle.width = targetWidth;
        } else if (stretch.indexOf('minWidth') !== -1) {
          sizeStyle.minWidth = targetWidth;
        }
      }

      var mergedStyle = _objectSpread({}, sizeStyle, {}, _this.getZIndexStyle(), {}, style, {
        opacity: status === 'stable' || !visible ? undefined : 0
      }); // ================= Motions =================


      var mergedMotion = _this.getMotion();

      var mergedMotionVisible = visible;

      if (visible && status !== 'beforeMotion' && status !== 'motion' && status !== 'stable') {
        mergedMotion.motionAppear = false;
        mergedMotion.motionEnter = false;
        mergedMotion.motionLeave = false;
      }

      if (status === 'afterAlign' || status === 'beforeMotion') {
        mergedMotionVisible = false;
      } // ================== Align ==================


      var mergedAlignDisabled = !visible || status !== 'align' && status !== 'aligned' && status !== 'stable'; // ================== Popup ==================

      var mergedPopupVisible = true;

      if (status === 'stable') {
        mergedPopupVisible = visible;
      } // Only remove popup since mask may still need animation


      if (destroyPopupOnHide && !mergedPopupVisible) {
        return null;
      }

      return _react.default.createElement(CSSMotion, Object.assign({
        visible: mergedMotionVisible
      }, mergedMotion, {
        removeOnLeave: false,
        onEnterEnd: _this.onMotionEnd,
        onLeaveEnd: _this.onMotionEnd
      }), function (_ref, motionRef) {
        var motionStyle = _ref.style,
            motionClassName = _ref.className;
        return _react.default.createElement(_rcAlign.default, {
          target: _this.getAlignTarget(),
          key: "popup",
          ref: _this.alignRef,
          monitorWindowResize: true,
          disabled: mergedAlignDisabled,
          align: align,
          onAlign: _this.onAlign
        }, _react.default.createElement(_PopupInner.default, {
          prefixCls: prefixCls,
          visible: mergedPopupVisible,
          hiddenClassName: hiddenClassName,
          className: (0, _classnames.default)(mergedClassName, motionClassName),
          ref: (0, _ref5.composeRef)(motionRef, _this.popupRef),
          onMouseEnter: onMouseEnter,
          onMouseLeave: onMouseLeave,
          onMouseDown: onMouseDown,
          onTouchStart: onTouchStart,
          style: _objectSpread({}, mergedStyle, {}, motionStyle)
        }, children));
      });
    };

    _this.renderMaskElement = function () {
      var _this$props4 = _this.props,
          mask = _this$props4.mask,
          maskMotion = _this$props4.maskMotion,
          maskTransitionName = _this$props4.maskTransitionName,
          maskAnimation = _this$props4.maskAnimation,
          prefixCls = _this$props4.prefixCls,
          visible = _this$props4.visible;

      if (!mask) {
        return null;
      }

      var motion = {};

      if (maskMotion && maskMotion.motionName) {
        motion = _objectSpread({
          motionAppear: true
        }, (0, _legacyUtil.getMotion)({
          motion: maskMotion,
          prefixCls: prefixCls,
          transitionName: maskTransitionName,
          animation: maskAnimation
        }));
      }

      return _react.default.createElement(CSSMotion, Object.assign({}, motion, {
        visible: visible,
        removeOnLeave: true
      }), function (_ref2) {
        var className = _ref2.className;
        return _react.default.createElement("div", {
          style: _this.getZIndexStyle(),
          key: "mask",
          className: (0, _classnames.default)("".concat(prefixCls, "-mask"), className)
        });
      });
    };

    return _this;
  }

  _createClass(Popup, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      this.componentDidUpdate();
    }
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate() {
      var status = this.state.status;
      var _this$props5 = this.props,
          getRootDomNode = _this$props5.getRootDomNode,
          visible = _this$props5.visible,
          stretch = _this$props5.stretch; // If there is a pending state update, cancel it, a new one will be set if necessary

      this.cancelFrameState();

      if (visible && status !== 'stable') {
        switch (status) {
          case null:
            {
              this.setStateOnNextFrame({
                status: stretch ? 'measure' : 'align'
              });
              break;
            }

          case 'afterAlign':
            {
              this.setStateOnNextFrame({
                status: supportMotion(this.getMotion()) ? 'beforeMotion' : 'stable'
              });
              break;
            }

          case 'AfterMotion':
            {
              this.setStateOnNextFrame({
                status: 'stable'
              });
              break;
            }

          default:
            {
              // Go to next status
              var queue = ['measure', 'align', null, 'beforeMotion', 'motion'];
              var index = queue.indexOf(status);
              var nextStatus = queue[index + 1];

              if (index !== -1 && nextStatus) {
                this.setStateOnNextFrame({
                  status: nextStatus
                });
              }
            }
        }
      } // Measure stretch size


      if (status === 'measure') {
        var $ele = getRootDomNode();

        if ($ele) {
          this.setStateOnNextFrame({
            targetHeight: $ele.offsetHeight,
            targetWidth: $ele.offsetWidth
          });
        }
      }
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      this.cancelFrameState();
    }
  }, {
    key: "getZIndexStyle",
    value: function getZIndexStyle() {
      var zIndex = this.props.zIndex;
      return {
        zIndex: zIndex
      };
    }
  }, {
    key: "render",
    value: function render() {
      return _react.default.createElement("div", null, this.renderMaskElement(), this.renderPopupElement());
    }
  }], [{
    key: "getDerivedStateFromProps",
    value: function getDerivedStateFromProps(_ref3, _ref4) {
      var visible = _ref3.visible,
          props = _objectWithoutProperties(_ref3, ["visible"]);

      var prevVisible = _ref4.prevVisible,
          status = _ref4.status;
      var newState = {
        prevVisible: visible,
        status: status
      };
      var mergedMotion = (0, _legacyUtil.getMotion)(props);

      if (prevVisible === null && visible === false) {
        // Init render should always be stable
        newState.status = 'stable';
      } else if (visible !== prevVisible) {
        if (visible || supportMotion(mergedMotion) && ['motion', 'AfterMotion', 'stable'].includes(status)) {
          newState.status = null;
        } else {
          newState.status = 'stable';
        }

        if (visible) {
          newState.alignClassName = null;
        }
      }

      return newState;
    }
  }]);

  return Popup;
}(_react.Component);

var _default = Popup;
/* eslint-enable */

exports.default = _default;