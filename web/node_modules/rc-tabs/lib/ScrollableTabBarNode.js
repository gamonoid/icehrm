"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _react = _interopRequireDefault(require("react"));

var _classnames5 = _interopRequireDefault(require("classnames"));

var _debounce = _interopRequireDefault(require("lodash/debounce"));

var _resizeObserverPolyfill = _interopRequireDefault(require("resize-observer-polyfill"));

var _utils = require("./utils");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var ScrollableTabBarNode = /*#__PURE__*/function (_React$Component) {
  _inherits(ScrollableTabBarNode, _React$Component);

  function ScrollableTabBarNode(props) {
    var _this;

    _classCallCheck(this, ScrollableTabBarNode);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(ScrollableTabBarNode).call(this, props));

    _this.prevTransitionEnd = function (e) {
      if (e.propertyName !== 'opacity') {
        return;
      }

      var container = _this.props.getRef('container');

      _this.scrollToActiveTab({
        target: container,
        currentTarget: container
      });
    };

    _this.scrollToActiveTab = function (e) {
      var activeTab = _this.props.getRef('activeTab');

      var navWrap = _this.props.getRef('navWrap');

      if (e && e.target !== e.currentTarget || !activeTab) {
        return;
      } // when not scrollable or enter scrollable first time, don't emit scrolling


      var needToSroll = _this.isNextPrevShown() && _this.lastNextPrevShown;

      _this.lastNextPrevShown = _this.isNextPrevShown();

      if (!needToSroll) {
        return;
      }

      var activeTabWH = _this.getScrollWH(activeTab);

      var navWrapNodeWH = _this.getOffsetWH(navWrap);

      var _assertThisInitialize = _assertThisInitialized(_this),
          offset = _assertThisInitialize.offset;

      var wrapOffset = _this.getOffsetLT(navWrap);

      var activeTabOffset = _this.getOffsetLT(activeTab);

      if (wrapOffset > activeTabOffset) {
        offset += wrapOffset - activeTabOffset;

        _this.setOffset(offset);
      } else if (wrapOffset + navWrapNodeWH < activeTabOffset + activeTabWH) {
        offset -= activeTabOffset + activeTabWH - (wrapOffset + navWrapNodeWH);

        _this.setOffset(offset);
      }
    };

    _this.prev = function (e) {
      _this.props.onPrevClick(e);

      var navWrapNode = _this.props.getRef('navWrap');

      var navWrapNodeWH = _this.getOffsetWH(navWrapNode);

      var _assertThisInitialize2 = _assertThisInitialized(_this),
          offset = _assertThisInitialize2.offset;

      _this.setOffset(offset + navWrapNodeWH);
    };

    _this.next = function (e) {
      _this.props.onNextClick(e);

      var navWrapNode = _this.props.getRef('navWrap');

      var navWrapNodeWH = _this.getOffsetWH(navWrapNode);

      var _assertThisInitialize3 = _assertThisInitialized(_this),
          offset = _assertThisInitialize3.offset;

      _this.setOffset(offset - navWrapNodeWH);
    };

    _this.offset = 0;
    _this.state = {
      next: false,
      prev: false
    };
    return _this;
  }

  _createClass(ScrollableTabBarNode, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var _this2 = this;

      this.componentDidUpdate();
      this.debouncedResize = (0, _debounce.default)(function () {
        _this2.setNextPrev();

        _this2.scrollToActiveTab();
      }, 200);
      this.resizeObserver = new _resizeObserverPolyfill.default(this.debouncedResize);
      this.resizeObserver.observe(this.props.getRef('container'));
    }
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps) {
      var props = this.props;

      if (prevProps && prevProps.tabBarPosition !== props.tabBarPosition) {
        this.setOffset(0);
        return;
      }

      var nextPrev = this.setNextPrev(); // wait next, prev show hide

      /* eslint react/no-did-update-set-state:0 */

      if (this.isNextPrevShown(this.state) !== this.isNextPrevShown(nextPrev)) {
        this.setState({}, this.scrollToActiveTab);
      } else if (!prevProps || props.activeKey !== prevProps.activeKey) {
        // can not use props.activeKey
        this.scrollToActiveTab();
      }
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      if (this.resizeObserver) {
        this.resizeObserver.disconnect();
      }

      if (this.debouncedResize && this.debouncedResize.cancel) {
        this.debouncedResize.cancel();
      }
    }
  }, {
    key: "setNextPrev",
    value: function setNextPrev() {
      var navNode = this.props.getRef('nav');
      var navTabsContainer = this.props.getRef('navTabsContainer');
      var navNodeWH = this.getScrollWH(navTabsContainer || navNode); // Add 1px to fix `offsetWidth` with decimal in Chrome not correct handle
      // https://github.com/ant-design/ant-design/issues/13423

      var containerWH = this.getOffsetWH(this.props.getRef('container')) + 1;
      var navWrapNodeWH = this.getOffsetWH(this.props.getRef('navWrap'));
      var offset = this.offset;
      var minOffset = containerWH - navNodeWH;
      var _this$state = this.state,
          next = _this$state.next,
          prev = _this$state.prev;

      if (minOffset >= 0) {
        next = false;
        this.setOffset(0, false);
        offset = 0;
      } else if (minOffset < offset) {
        next = true;
      } else {
        next = false; // Fix https://github.com/ant-design/ant-design/issues/8861
        // Test with container offset which is stable
        // and set the offset of the nav wrap node

        var realOffset = navWrapNodeWH - navNodeWH;
        this.setOffset(realOffset, false);
        offset = realOffset;
      }

      if (offset < 0) {
        prev = true;
      } else {
        prev = false;
      }

      this.setNext(next);
      this.setPrev(prev);
      return {
        next: next,
        prev: prev
      };
    }
  }, {
    key: "getOffsetWH",
    value: function getOffsetWH(node) {
      var tabBarPosition = this.props.tabBarPosition;
      var prop = 'offsetWidth';

      if (tabBarPosition === 'left' || tabBarPosition === 'right') {
        prop = 'offsetHeight';
      }

      return node[prop];
    }
  }, {
    key: "getScrollWH",
    value: function getScrollWH(node) {
      var tabBarPosition = this.props.tabBarPosition;
      var prop = 'scrollWidth';

      if (tabBarPosition === 'left' || tabBarPosition === 'right') {
        prop = 'scrollHeight';
      }

      return node[prop];
    }
  }, {
    key: "getOffsetLT",
    value: function getOffsetLT(node) {
      var tabBarPosition = this.props.tabBarPosition;
      var prop = 'left';

      if (tabBarPosition === 'left' || tabBarPosition === 'right') {
        prop = 'top';
      }

      return node.getBoundingClientRect()[prop];
    }
  }, {
    key: "setOffset",
    value: function setOffset(offset) {
      var checkNextPrev = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
      var target = Math.min(0, offset);

      if (this.offset !== target) {
        this.offset = target;
        var navOffset = {};
        var tabBarPosition = this.props.tabBarPosition;
        var navStyle = this.props.getRef('nav').style;
        var transformSupported = (0, _utils.isTransform3dSupported)(navStyle);

        if (tabBarPosition === 'left' || tabBarPosition === 'right') {
          if (transformSupported) {
            navOffset = {
              value: "translate3d(0,".concat(target, "px,0)")
            };
          } else {
            navOffset = {
              name: 'top',
              value: "".concat(target, "px")
            };
          }
        } else if (transformSupported) {
          if (this.props.direction === 'rtl') {
            target = -target;
          }

          navOffset = {
            value: "translate3d(".concat(target, "px,0,0)")
          };
        } else {
          navOffset = {
            name: 'left',
            value: "".concat(target, "px")
          };
        }

        if (transformSupported) {
          (0, _utils.setTransform)(navStyle, navOffset.value);
        } else {
          navStyle[navOffset.name] = navOffset.value;
        }

        if (checkNextPrev) {
          this.setNextPrev();
        }
      }
    }
  }, {
    key: "setPrev",
    value: function setPrev(v) {
      if (this.state.prev !== v) {
        this.setState({
          prev: v
        });
      }
    }
  }, {
    key: "setNext",
    value: function setNext(v) {
      if (this.state.next !== v) {
        this.setState({
          next: v
        });
      }
    }
  }, {
    key: "isNextPrevShown",
    value: function isNextPrevShown(state) {
      if (state) {
        return state.next || state.prev;
      }

      return this.state.next || this.state.prev;
    }
  }, {
    key: "render",
    value: function render() {
      var _classnames, _classnames2, _classnames3, _classnames4;

      var _this$state2 = this.state,
          next = _this$state2.next,
          prev = _this$state2.prev;
      var _this$props = this.props,
          prefixCls = _this$props.prefixCls,
          scrollAnimated = _this$props.scrollAnimated,
          navWrapper = _this$props.navWrapper,
          prevIcon = _this$props.prevIcon,
          nextIcon = _this$props.nextIcon;
      var showNextPrev = prev || next;

      var prevButton = _react.default.createElement("span", {
        onClick: prev ? this.prev : null,
        unselectable: "unselectable",
        className: (0, _classnames5.default)((_classnames = {}, _defineProperty(_classnames, "".concat(prefixCls, "-tab-prev"), 1), _defineProperty(_classnames, "".concat(prefixCls, "-tab-btn-disabled"), !prev), _defineProperty(_classnames, "".concat(prefixCls, "-tab-arrow-show"), showNextPrev), _classnames)),
        onTransitionEnd: this.prevTransitionEnd
      }, prevIcon || _react.default.createElement("span", {
        className: "".concat(prefixCls, "-tab-prev-icon")
      }));

      var nextButton = _react.default.createElement("span", {
        onClick: next ? this.next : null,
        unselectable: "unselectable",
        className: (0, _classnames5.default)((_classnames2 = {}, _defineProperty(_classnames2, "".concat(prefixCls, "-tab-next"), 1), _defineProperty(_classnames2, "".concat(prefixCls, "-tab-btn-disabled"), !next), _defineProperty(_classnames2, "".concat(prefixCls, "-tab-arrow-show"), showNextPrev), _classnames2))
      }, nextIcon || _react.default.createElement("span", {
        className: "".concat(prefixCls, "-tab-next-icon")
      }));

      var navClassName = "".concat(prefixCls, "-nav");
      var navClasses = (0, _classnames5.default)((_classnames3 = {}, _defineProperty(_classnames3, navClassName, true), _defineProperty(_classnames3, scrollAnimated ? "".concat(navClassName, "-animated") : "".concat(navClassName, "-no-animated"), true), _classnames3));
      return _react.default.createElement("div", {
        className: (0, _classnames5.default)((_classnames4 = {}, _defineProperty(_classnames4, "".concat(prefixCls, "-nav-container"), 1), _defineProperty(_classnames4, "".concat(prefixCls, "-nav-container-scrolling"), showNextPrev), _classnames4)),
        key: "container",
        ref: this.props.saveRef('container')
      }, prevButton, nextButton, _react.default.createElement("div", {
        className: "".concat(prefixCls, "-nav-wrap"),
        ref: this.props.saveRef('navWrap')
      }, _react.default.createElement("div", {
        className: "".concat(prefixCls, "-nav-scroll")
      }, _react.default.createElement("div", {
        className: navClasses,
        ref: this.props.saveRef('nav')
      }, navWrapper(this.props.children)))));
    }
  }]);

  return ScrollableTabBarNode;
}(_react.default.Component);

exports.default = ScrollableTabBarNode;
ScrollableTabBarNode.defaultProps = {
  tabBarPosition: 'left',
  prefixCls: '',
  scrollAnimated: true,
  onPrevClick: function onPrevClick() {},
  onNextClick: function onNextClick() {},
  navWrapper: function navWrapper(ele) {
    return ele;
  }
};