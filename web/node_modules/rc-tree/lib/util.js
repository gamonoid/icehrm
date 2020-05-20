"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.arrDel = arrDel;
exports.arrAdd = arrAdd;
exports.posToArr = posToArr;
exports.getPosition = getPosition;
exports.isTreeNode = isTreeNode;
exports.getDragNodesKeys = getDragNodesKeys;
exports.calcDropPosition = calcDropPosition;
exports.calcSelectedKeys = calcSelectedKeys;
exports.convertDataToTree = convertDataToTree;
exports.parseCheckedKeys = parseCheckedKeys;
exports.conductExpandParent = conductExpandParent;
exports.getDataAndAria = getDataAndAria;

var _react = _interopRequireDefault(require("react"));

var _warning = _interopRequireDefault(require("rc-util/lib/warning"));

var _TreeNode = _interopRequireDefault(require("./TreeNode"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

var DRAG_SIDE_RANGE = 0.25;
var DRAG_MIN_GAP = 2;

function arrDel(list, value) {
  var clone = list.slice();
  var index = clone.indexOf(value);

  if (index >= 0) {
    clone.splice(index, 1);
  }

  return clone;
}

function arrAdd(list, value) {
  var clone = list.slice();

  if (clone.indexOf(value) === -1) {
    clone.push(value);
  }

  return clone;
}

function posToArr(pos) {
  return pos.split('-');
}

function getPosition(level, index) {
  return "".concat(level, "-").concat(index);
}

function isTreeNode(node) {
  return node && node.type && node.type.isTreeNode;
}

function getDragNodesKeys(dragNodeKey, keyEntities) {
  var dragNodesKeys = [dragNodeKey];
  var entity = keyEntities[dragNodeKey];

  function dig() {
    var list = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
    list.forEach(function (_ref) {
      var key = _ref.key,
          children = _ref.children;
      dragNodesKeys.push(key);
      dig(children);
    });
  }

  dig(entity.children);
  return dragNodesKeys;
} // Only used when drag, not affect SSR.


function calcDropPosition(event, treeNode) {
  var clientY = event.clientY;

  var _treeNode$selectHandl = treeNode.selectHandle.getBoundingClientRect(),
      top = _treeNode$selectHandl.top,
      bottom = _treeNode$selectHandl.bottom,
      height = _treeNode$selectHandl.height;

  var des = Math.max(height * DRAG_SIDE_RANGE, DRAG_MIN_GAP);

  if (clientY <= top + des) {
    return -1;
  }

  if (clientY >= bottom - des) {
    return 1;
  }

  return 0;
}
/**
 * Return selectedKeys according with multiple prop
 * @param selectedKeys
 * @param props
 * @returns [string]
 */


function calcSelectedKeys(selectedKeys, props) {
  if (!selectedKeys) return undefined;
  var multiple = props.multiple;

  if (multiple) {
    return selectedKeys.slice();
  }

  if (selectedKeys.length) {
    return [selectedKeys[0]];
  }

  return selectedKeys;
}

var internalProcessProps = function internalProcessProps(props) {
  return props;
};

function convertDataToTree(treeData, processor) {
  if (!treeData) return [];

  var _ref2 = processor || {},
      _ref2$processProps = _ref2.processProps,
      processProps = _ref2$processProps === void 0 ? internalProcessProps : _ref2$processProps;

  var list = Array.isArray(treeData) ? treeData : [treeData];
  return list.map(function (_ref3) {
    var children = _ref3.children,
        props = _objectWithoutProperties(_ref3, ["children"]);

    var childrenNodes = convertDataToTree(children, processor);
    return _react.default.createElement(_TreeNode.default, Object.assign({}, processProps(props)), childrenNodes);
  });
}
/**
 * Parse `checkedKeys` to { checkedKeys, halfCheckedKeys } style
 */


function parseCheckedKeys(keys) {
  if (!keys) {
    return null;
  } // Convert keys to object format


  var keyProps;

  if (Array.isArray(keys)) {
    // [Legacy] Follow the api doc
    keyProps = {
      checkedKeys: keys,
      halfCheckedKeys: undefined
    };
  } else if (_typeof(keys) === 'object') {
    keyProps = {
      checkedKeys: keys.checked || undefined,
      halfCheckedKeys: keys.halfChecked || undefined
    };
  } else {
    (0, _warning.default)(false, '`checkedKeys` is not an array or an object');
    return null;
  }

  return keyProps;
}
/**
 * If user use `autoExpandParent` we should get the list of parent node
 * @param keyList
 * @param keyEntities
 */


function conductExpandParent(keyList, keyEntities) {
  var expandedKeys = {};

  function conductUp(key) {
    if (expandedKeys[key]) return;
    var entity = keyEntities[key];
    if (!entity) return;
    expandedKeys[key] = true;
    var parent = entity.parent,
        node = entity.node;
    if (node.disabled) return;

    if (parent) {
      conductUp(parent.key);
    }
  }

  (keyList || []).forEach(function (key) {
    conductUp(key);
  });
  return Object.keys(expandedKeys);
}
/**
 * Returns only the data- and aria- key/value pairs
 */


function getDataAndAria(props) {
  var omitProps = {};
  Object.keys(props).forEach(function (key) {
    if (key.startsWith('data-') || key.startsWith('aria-')) {
      omitProps[key] = props[key];
    }
  });
  return omitProps;
}