"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _react = _interopRequireDefault(require("react"));

var _OptionList = _interopRequireDefault(require("./OptionList"));

var _Option = _interopRequireDefault(require("./Option"));

var _OptGroup = _interopRequireDefault(require("./OptGroup"));

var _legacyUtil = require("./utils/legacyUtil");

var _valueUtil = require("./utils/valueUtil");

var _generate = _interopRequireDefault(require("./generate"));

var _warningPropsUtil = _interopRequireDefault(require("./utils/warningPropsUtil"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

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

var RefSelect = (0, _generate.default)({
  prefixCls: 'rc-select',
  components: {
    optionList: _OptionList.default
  },
  convertChildrenToData: _legacyUtil.convertChildrenToData,
  flattenOptions: _valueUtil.flattenOptions,
  getLabeledValue: _valueUtil.getLabeledValue,
  filterOptions: _valueUtil.filterOptions,
  isValueDisabled: _valueUtil.isValueDisabled,
  findValueOption: _valueUtil.findValueOption,
  warningProps: _warningPropsUtil.default,
  fillOptionsWithMissingValue: _valueUtil.fillOptionsWithMissingValue
});
/**
 * Typescript not support generic with function component,
 * we have to wrap an class component to handle this.
 */

var Select = /*#__PURE__*/function (_React$Component) {
  _inherits(Select, _React$Component);

  var _super = _createSuper(Select);

  function Select() {
    var _this;

    _classCallCheck(this, Select);

    _this = _super.apply(this, arguments);
    _this.selectRef = _react.default.createRef();

    _this.focus = function () {
      _this.selectRef.current.focus();
    };

    _this.blur = function () {
      _this.selectRef.current.blur();
    };

    return _this;
  }

  _createClass(Select, [{
    key: "render",
    value: function render() {
      return _react.default.createElement(RefSelect, Object.assign({
        ref: this.selectRef
      }, this.props));
    }
  }]);

  return Select;
}(_react.default.Component);

Select.Option = _Option.default;
Select.OptGroup = _OptGroup.default;
var _default = Select;
exports.default = _default;