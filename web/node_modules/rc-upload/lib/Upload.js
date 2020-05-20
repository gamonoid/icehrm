'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _extends2 = require('babel-runtime/helpers/extends');

var _extends3 = _interopRequireDefault(_extends2);

var _classCallCheck2 = require('babel-runtime/helpers/classCallCheck');

var _classCallCheck3 = _interopRequireDefault(_classCallCheck2);

var _createClass2 = require('babel-runtime/helpers/createClass');

var _createClass3 = _interopRequireDefault(_createClass2);

var _possibleConstructorReturn2 = require('babel-runtime/helpers/possibleConstructorReturn');

var _possibleConstructorReturn3 = _interopRequireDefault(_possibleConstructorReturn2);

var _inherits2 = require('babel-runtime/helpers/inherits');

var _inherits3 = _interopRequireDefault(_inherits2);

var _react = require('react');

var _react2 = _interopRequireDefault(_react);

var _AjaxUploader = require('./AjaxUploader');

var _AjaxUploader2 = _interopRequireDefault(_AjaxUploader);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

/* eslint react/prop-types:0 */
function empty() {}

var Upload = function (_Component) {
  (0, _inherits3['default'])(Upload, _Component);

  function Upload() {
    var _ref;

    var _temp, _this, _ret;

    (0, _classCallCheck3['default'])(this, Upload);

    for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    return _ret = (_temp = (_this = (0, _possibleConstructorReturn3['default'])(this, (_ref = Upload.__proto__ || Object.getPrototypeOf(Upload)).call.apply(_ref, [this].concat(args))), _this), _this.saveUploader = function (node) {
      _this.uploader = node;
    }, _temp), (0, _possibleConstructorReturn3['default'])(_this, _ret);
  }

  (0, _createClass3['default'])(Upload, [{
    key: 'componentDidMount',
    value: function componentDidMount() {
      this.props.onReady();
    }
  }, {
    key: 'abort',
    value: function abort(file) {
      this.uploader.abort(file);
    }
  }, {
    key: 'render',
    value: function render() {
      return _react2['default'].createElement(_AjaxUploader2['default'], (0, _extends3['default'])({}, this.props, { ref: this.saveUploader }));
    }
  }]);
  return Upload;
}(_react.Component);

Upload.defaultProps = {
  component: 'span',
  prefixCls: 'rc-upload',
  data: {},
  headers: {},
  name: 'file',
  multipart: false,
  onReady: empty,
  onStart: empty,
  onError: empty,
  onSuccess: empty,
  multiple: false,
  beforeUpload: null,
  customRequest: null,
  withCredentials: false,
  openFileDialogOnClick: true
};
exports['default'] = Upload;
module.exports = exports['default'];