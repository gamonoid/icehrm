"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.MentionsContextConsumer = exports.MentionsContextProvider = void 0;

var React = _interopRequireWildcard(require("react"));

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = Object.defineProperty && Object.getOwnPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : {}; if (desc.get || desc.set) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } } newObj.default = obj; return newObj; } }

/* tslint:disable: no-object-literal-type-assertion */
// We will never use default, here only to fix TypeScript warning
var MentionsContext = React.createContext(null);
var MentionsContextProvider = MentionsContext.Provider;
exports.MentionsContextProvider = MentionsContextProvider;
var MentionsContextConsumer = MentionsContext.Consumer;
exports.MentionsContextConsumer = MentionsContextConsumer;