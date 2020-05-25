function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

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
import debounce from 'lodash/debounce';
import SlickCarousel from '@ant-design/react-slick';
import classNames from 'classnames';
import { ConfigConsumer } from '../config-provider';

var Carousel = /*#__PURE__*/function (_React$Component) {
  _inherits(Carousel, _React$Component);

  var _super = _createSuper(Carousel);

  function Carousel(props) {
    var _this;

    _classCallCheck(this, Carousel);

    _this = _super.call(this, props);

    _this.saveSlick = function (node) {
      _this.slick = node;
    };

    _this.onWindowResized = function () {
      // Fix https://github.com/ant-design/ant-design/issues/2550
      var autoplay = _this.props.autoplay;

      if (autoplay && _this.slick && _this.slick.innerSlider && _this.slick.innerSlider.autoPlay) {
        _this.slick.innerSlider.autoPlay();
      }
    };

    _this.renderCarousel = function (_ref) {
      var _classNames;

      var getPrefixCls = _ref.getPrefixCls,
          direction = _ref.direction;

      var _a;

      var props = _extends({}, _this.props);

      if (props.effect === 'fade') {
        props.fade = true;
      }

      var prefixCls = getPrefixCls('carousel', props.prefixCls);
      var dotsClass = 'slick-dots';

      var dotPosition = _this.getDotPosition();

      props.vertical = dotPosition === 'left' || dotPosition === 'right';
      var enableDots = !!props.dots;
      var dsClass = classNames(dotsClass, "".concat(dotsClass, "-").concat(dotPosition || 'bottom'), typeof props.dots === 'boolean' ? false : (_a = props.dots) === null || _a === void 0 ? void 0 : _a.className);
      var className = classNames(prefixCls, (_classNames = {}, _defineProperty(_classNames, "".concat(prefixCls, "-rtl"), direction === 'rtl'), _defineProperty(_classNames, "".concat(prefixCls, "-vertical"), props.vertical), _classNames));
      return /*#__PURE__*/React.createElement("div", {
        className: className
      }, /*#__PURE__*/React.createElement(SlickCarousel, _extends({
        ref: _this.saveSlick
      }, props, {
        dots: enableDots,
        dotsClass: dsClass
      })));
    };

    _this.onWindowResized = debounce(_this.onWindowResized, 500, {
      leading: false
    });
    return _this;
  }

  _createClass(Carousel, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var autoplay = this.props.autoplay;

      if (autoplay) {
        window.addEventListener('resize', this.onWindowResized);
      } // https://github.com/ant-design/ant-design/issues/7191


      this.innerSlider = this.slick && this.slick.innerSlider;
    }
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps) {
      if (React.Children.count(this.props.children) !== React.Children.count(prevProps.children)) {
        this.goTo(this.props.initialSlide || 0, false);
      }
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      var autoplay = this.props.autoplay;

      if (autoplay) {
        window.removeEventListener('resize', this.onWindowResized);
        this.onWindowResized.cancel();
      }
    }
  }, {
    key: "getDotPosition",
    value: function getDotPosition() {
      var _this$props$dotPositi = this.props.dotPosition,
          dotPosition = _this$props$dotPositi === void 0 ? 'bottom' : _this$props$dotPositi;
      return dotPosition;
    }
  }, {
    key: "next",
    value: function next() {
      this.slick.slickNext();
    }
  }, {
    key: "prev",
    value: function prev() {
      this.slick.slickPrev();
    }
  }, {
    key: "goTo",
    value: function goTo(slide) {
      var dontAnimate = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      this.slick.slickGoTo(slide, dontAnimate);
    }
  }, {
    key: "render",
    value: function render() {
      return /*#__PURE__*/React.createElement(ConfigConsumer, null, this.renderCarousel);
    }
  }]);

  return Carousel;
}(React.Component);

export { Carousel as default };
Carousel.defaultProps = {
  dots: true,
  arrows: false,
  draggable: false
};