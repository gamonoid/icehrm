"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var React = _interopRequireWildcard(require("react"));

var _rcTrigger = _interopRequireDefault(require("rc-trigger"));

var _classnames = _interopRequireDefault(require("classnames"));

var _placements = _interopRequireDefault(require("./placements"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; if (obj != null) { var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { if (!(Symbol.iterator in Object(arr) || Object.prototype.toString.call(arr) === "[object Arguments]")) { return; } var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

function Dropdown(props, ref) {
  var _props$prefixCls = props.prefixCls,
      prefixCls = _props$prefixCls === void 0 ? 'rc-dropdown' : _props$prefixCls,
      transitionName = props.transitionName,
      animation = props.animation,
      align = props.align,
      _props$placement = props.placement,
      placement = _props$placement === void 0 ? 'bottomLeft' : _props$placement,
      _props$placements = props.placements,
      placements = _props$placements === void 0 ? _placements.default : _props$placements,
      getPopupContainer = props.getPopupContainer,
      showAction = props.showAction,
      hideAction = props.hideAction,
      overlayClassName = props.overlayClassName,
      overlayStyle = props.overlayStyle,
      visible = props.visible,
      _props$trigger = props.trigger,
      trigger = _props$trigger === void 0 ? ['hover'] : _props$trigger,
      otherProps = _objectWithoutProperties(props, ["prefixCls", "transitionName", "animation", "align", "placement", "placements", "getPopupContainer", "showAction", "hideAction", "overlayClassName", "overlayStyle", "visible", "trigger"]);

  var _React$useState = React.useState(),
      _React$useState2 = _slicedToArray(_React$useState, 2),
      triggerVisible = _React$useState2[0],
      setTriggerVisible = _React$useState2[1];

  var mergedVisible = 'visible' in props ? visible : triggerVisible;
  var triggerRef = React.useRef(null);
  React.useImperativeHandle(ref, function () {
    return triggerRef.current;
  });

  var getOverlayElement = function getOverlayElement() {
    var overlay = props.overlay;
    var overlayElement;

    if (typeof overlay === 'function') {
      overlayElement = overlay();
    } else {
      overlayElement = overlay;
    }

    return overlayElement;
  };

  var onClick = function onClick(e) {
    var onOverlayClick = props.onOverlayClick;
    var overlayProps = getOverlayElement().props;
    setTriggerVisible(false);

    if (onOverlayClick) {
      onOverlayClick(e);
    }

    if (overlayProps.onClick) {
      overlayProps.onClick(e);
    }
  };

  var onVisibleChange = function onVisibleChange(visible) {
    var onVisibleChange = props.onVisibleChange;
    setTriggerVisible(visible);

    if (typeof onVisibleChange === 'function') {
      onVisibleChange(visible);
    }
  };

  var getMenuElement = function getMenuElement() {
    var overlayElement = getOverlayElement();
    var extraOverlayProps = {
      prefixCls: "".concat(prefixCls, "-menu"),
      onClick: onClick
    };

    if (typeof overlayElement.type === 'string') {
      delete extraOverlayProps.prefixCls;
    }

    return React.cloneElement(overlayElement, extraOverlayProps);
  };

  var getMenuElementOrLambda = function getMenuElementOrLambda() {
    var overlay = props.overlay;

    if (typeof overlay === 'function') {
      return getMenuElement;
    }

    return getMenuElement();
  };

  var getMinOverlayWidthMatchTrigger = function getMinOverlayWidthMatchTrigger() {
    var minOverlayWidthMatchTrigger = props.minOverlayWidthMatchTrigger,
        alignPoint = props.alignPoint;

    if ('minOverlayWidthMatchTrigger' in props) {
      return minOverlayWidthMatchTrigger;
    }

    return !alignPoint;
  };

  var getOpenClassName = function getOpenClassName() {
    var openClassName = props.openClassName;

    if (openClassName !== undefined) {
      return openClassName;
    }

    return "".concat(prefixCls, "-open");
  };

  var renderChildren = function renderChildren() {
    var children = props.children;
    var childrenProps = children.props ? children.props : {};
    var childClassName = (0, _classnames.default)(childrenProps.className, getOpenClassName());
    return triggerVisible && children ? React.cloneElement(children, {
      className: childClassName
    }) : children;
  };

  var triggerHideAction = hideAction;

  if (!triggerHideAction && trigger.indexOf('contextMenu') !== -1) {
    triggerHideAction = ['click'];
  }

  return React.createElement(_rcTrigger.default, Object.assign({}, otherProps, {
    prefixCls: prefixCls,
    ref: triggerRef,
    popupClassName: overlayClassName,
    popupStyle: overlayStyle,
    builtinPlacements: placements,
    action: trigger,
    showAction: showAction,
    hideAction: triggerHideAction || [],
    popupPlacement: placement,
    popupAlign: align,
    popupTransitionName: transitionName,
    popupAnimation: animation,
    popupVisible: mergedVisible,
    stretch: getMinOverlayWidthMatchTrigger() ? 'minWidth' : '',
    popup: getMenuElementOrLambda(),
    onPopupVisibleChange: onVisibleChange,
    getPopupContainer: getPopupContainer
  }), renderChildren());
}

var _default = React.forwardRef(Dropdown);

exports.default = _default;