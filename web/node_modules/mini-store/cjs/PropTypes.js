"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
var prop_types_1 = __importDefault(require("prop-types"));
exports.storeShape = prop_types_1.default.shape({
    subscribe: prop_types_1.default.func.isRequired,
    setState: prop_types_1.default.func.isRequired,
    getState: prop_types_1.default.func.isRequired,
});
