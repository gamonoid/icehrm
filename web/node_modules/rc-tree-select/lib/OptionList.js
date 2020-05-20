"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _react = _interopRequireDefault(require("react"));

var _KeyCode = _interopRequireDefault(require("rc-util/lib/KeyCode"));

var _useMemo = _interopRequireDefault(require("rc-util/lib/hooks/useMemo"));

var _rcTree = _interopRequireDefault(require("rc-tree"));

var _Context = require("./Context");

var _useKeyValueMapping3 = _interopRequireDefault(require("./hooks/useKeyValueMapping"));

var _useKeyValueMap3 = _interopRequireDefault(require("./hooks/useKeyValueMap"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { if (!(Symbol.iterator in Object(arr) || Object.prototype.toString.call(arr) === "[object Arguments]")) { return; } var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var HIDDEN_STYLE = {
  width: 0,
  height: 0,
  display: 'flex',
  overflow: 'hidden',
  opacity: 0,
  border: 0,
  padding: 0,
  margin: 0
};

var OptionList = function OptionList(props, ref) {
  var prefixCls = props.prefixCls,
      height = props.height,
      itemHeight = props.itemHeight,
      virtual = props.virtual,
      options = props.options,
      flattenOptions = props.flattenOptions,
      multiple = props.multiple,
      searchValue = props.searchValue,
      onSelect = props.onSelect,
      onToggleOpen = props.onToggleOpen,
      open = props.open,
      notFoundContent = props.notFoundContent;

  var _React$useContext = _react.default.useContext(_Context.SelectContext),
      checkable = _React$useContext.checkable,
      checkedKeys = _React$useContext.checkedKeys,
      halfCheckedKeys = _React$useContext.halfCheckedKeys,
      treeExpandedKeys = _React$useContext.treeExpandedKeys,
      treeDefaultExpandAll = _React$useContext.treeDefaultExpandAll,
      treeDefaultExpandedKeys = _React$useContext.treeDefaultExpandedKeys,
      onTreeExpand = _React$useContext.onTreeExpand,
      treeIcon = _React$useContext.treeIcon,
      showTreeIcon = _React$useContext.showTreeIcon,
      switcherIcon = _React$useContext.switcherIcon,
      treeLine = _React$useContext.treeLine,
      treeNodeFilterProp = _React$useContext.treeNodeFilterProp,
      loadData = _React$useContext.loadData,
      treeLoadedKeys = _React$useContext.treeLoadedKeys,
      treeMotion = _React$useContext.treeMotion,
      onTreeLoad = _React$useContext.onTreeLoad;

  var treeRef = _react.default.useRef();

  var memoOptions = (0, _useMemo.default)(function () {
    return options;
  }, [open, options], function (prev, next) {
    return next[0] && prev[1] !== next[1];
  });

  var _useKeyValueMap = (0, _useKeyValueMap3.default)(flattenOptions),
      _useKeyValueMap2 = _slicedToArray(_useKeyValueMap, 2),
      cacheKeyMap = _useKeyValueMap2[0],
      cacheValueMap = _useKeyValueMap2[1];

  var _useKeyValueMapping = (0, _useKeyValueMapping3.default)(cacheKeyMap, cacheValueMap),
      _useKeyValueMapping2 = _slicedToArray(_useKeyValueMapping, 2),
      getEntityByKey = _useKeyValueMapping2[0],
      getEntityByValue = _useKeyValueMapping2[1]; // ========================== Values ==========================


  var valueKeys = _react.default.useMemo(function () {
    return checkedKeys.map(function (val) {
      var entity = getEntityByValue(val);
      return entity ? entity.key : null;
    });
  }, [checkedKeys]);

  var mergedCheckedKeys = _react.default.useMemo(function () {
    if (!checkable) {
      return null;
    }

    return {
      checked: valueKeys,
      halfChecked: halfCheckedKeys
    };
  }, [valueKeys, halfCheckedKeys, checkable]); // ========================== Scroll ==========================


  _react.default.useEffect(function () {
    // Single mode should scroll to current key
    if (open && !multiple && valueKeys.length) {
      var _treeRef$current;

      (_treeRef$current = treeRef.current) === null || _treeRef$current === void 0 ? void 0 : _treeRef$current.scrollTo({
        key: valueKeys[0]
      });
    }
  }, [open]); // ========================== Search ==========================


  var lowerSearchValue = String(searchValue).toLowerCase();

  var filterTreeNode = function filterTreeNode(treeNode) {
    if (!lowerSearchValue) {
      return false;
    }

    return String(treeNode[treeNodeFilterProp]).toLowerCase().includes(lowerSearchValue);
  }; // =========================== Keys ===========================


  var _React$useState = _react.default.useState(treeDefaultExpandedKeys),
      _React$useState2 = _slicedToArray(_React$useState, 2),
      expandedKeys = _React$useState2[0],
      setExpandedKeys = _React$useState2[1];

  var _React$useState3 = _react.default.useState(null),
      _React$useState4 = _slicedToArray(_React$useState3, 2),
      searchExpandedKeys = _React$useState4[0],
      setSearchExpandedKeys = _React$useState4[1];

  var mergedExpandedKeys = _react.default.useMemo(function () {
    if (treeExpandedKeys) {
      return _toConsumableArray(treeExpandedKeys);
    }

    return searchValue ? searchExpandedKeys : expandedKeys;
  }, [expandedKeys, searchExpandedKeys, lowerSearchValue, treeExpandedKeys]);

  _react.default.useEffect(function () {
    if (searchValue) {
      setSearchExpandedKeys(flattenOptions.map(function (o) {
        return o.key;
      }));
    }
  }, [searchValue]);

  var onInternalExpand = function onInternalExpand(keys) {
    setExpandedKeys(keys);
    setSearchExpandedKeys(keys);

    if (onTreeExpand) {
      onTreeExpand(keys);
    }
  }; // ========================== Events ==========================


  var onListMouseDown = function onListMouseDown(event) {
    event.preventDefault();
  };

  var onInternalSelect = function onInternalSelect(_, _ref) {
    var key = _ref.node.key;
    var entity = getEntityByKey(key, checkable ? 'checkbox' : 'select');

    if (entity !== null) {
      onSelect(entity.data.value, {
        selected: !checkedKeys.includes(entity.data.value)
      });
    }

    if (!multiple) {
      onToggleOpen(false);
    }
  }; // ========================= Keyboard =========================


  var _React$useState5 = _react.default.useState(null),
      _React$useState6 = _slicedToArray(_React$useState5, 2),
      activeKey = _React$useState6[0],
      setActiveKey = _React$useState6[1];

  var activeEntity = getEntityByKey(activeKey);

  _react.default.useImperativeHandle(ref, function () {
    return {
      onKeyDown: function onKeyDown(event) {
        var _treeRef$current2;

        var which = event.which;

        switch (which) {
          // >>> Arrow keys
          case _KeyCode.default.UP:
          case _KeyCode.default.DOWN:
          case _KeyCode.default.LEFT:
          case _KeyCode.default.RIGHT:
            (_treeRef$current2 = treeRef.current) === null || _treeRef$current2 === void 0 ? void 0 : _treeRef$current2.onKeyDown(event);
            break;
          // >>> Select item

          case _KeyCode.default.ENTER:
            {
              if (activeEntity !== null) {
                onInternalSelect(null, {
                  node: {
                    key: activeKey
                  },
                  selected: !checkedKeys.includes(activeEntity.data.value)
                });
              }

              break;
            }
          // >>> Close

          case _KeyCode.default.ESC:
            {
              onToggleOpen(false);
            }
        }
      },
      onKeyUp: function onKeyUp() {}
    };
  }); // ========================== Render ==========================


  if (memoOptions.length === 0) {
    return _react.default.createElement("div", {
      role: "listbox",
      className: "".concat(prefixCls, "-empty"),
      onMouseDown: onListMouseDown
    }, notFoundContent);
  }

  var treeProps = {};

  if (treeLoadedKeys) {
    treeProps.loadedKeys = treeLoadedKeys;
  }

  if (mergedExpandedKeys) {
    treeProps.expandedKeys = mergedExpandedKeys;
  }

  return _react.default.createElement("div", {
    onMouseDown: onListMouseDown
  }, activeEntity && open && _react.default.createElement("span", {
    style: HIDDEN_STYLE,
    "aria-live": "assertive"
  }, activeEntity.data.value), _react.default.createElement(_rcTree.default, Object.assign({
    ref: treeRef,
    focusable: false,
    prefixCls: "".concat(prefixCls, "-tree"),
    treeData: memoOptions,
    height: height,
    itemHeight: itemHeight,
    virtual: virtual,
    multiple: multiple,
    icon: treeIcon,
    showIcon: showTreeIcon,
    switcherIcon: switcherIcon,
    showLine: treeLine,
    loadData: searchValue ? null : loadData,
    motion: treeMotion,
    // We handle keys by out instead tree self
    checkable: checkable,
    checkStrictly: true,
    checkedKeys: mergedCheckedKeys,
    selectedKeys: !checkable ? valueKeys : [],
    defaultExpandAll: treeDefaultExpandAll
  }, treeProps, {
    // Proxy event out
    onActiveChange: setActiveKey,
    onSelect: onInternalSelect,
    onCheck: onInternalSelect,
    onExpand: onInternalExpand,
    onLoad: onTreeLoad,
    filterTreeNode: filterTreeNode
  })));
};

var RefOptionList = _react.default.forwardRef(OptionList);

RefOptionList.displayName = 'OptionList';
var _default = RefOptionList;
exports.default = _default;