import React from 'react';
import { SelectProps, RefSelectProps } from 'rc-select/lib/generate';
import { IconType } from 'rc-tree/lib/interface';
import { FilterFunc } from 'rc-select/lib/interface/generator';
import { Key, DefaultValueType, DataNode, LabelValueType, SimpleModeConfig, ChangeEventExtra, LegacyDataNode } from './interface';
import { CheckedStrategy, SHOW_ALL, SHOW_PARENT, SHOW_CHILD } from './utils/strategyUtil';
export interface TreeSelectProps<ValueType = DefaultValueType> extends Omit<SelectProps<DataNode[], ValueType>, 'onChange' | 'mode' | 'menuItemSelectedIcon' | 'dropdownRender' | 'dropdownAlign' | 'backfill' | 'getInputElement' | 'optionLabelProp' | 'tokenSeparators' | 'filterOption'> {
    multiple?: boolean;
    showArrow?: boolean;
    showSearch?: boolean;
    open?: boolean;
    defaultOpen?: boolean;
    value?: ValueType;
    defaultValue?: ValueType;
    disabled?: boolean;
    placeholder?: React.ReactNode;
    /** @deprecated Use `searchValue` instead */
    inputValue?: string;
    searchValue?: string;
    autoClearSearchValue?: boolean;
    maxTagTextLength?: number;
    maxTagCount?: number;
    maxTagPlaceholder?: (omittedValues: LabelValueType[]) => React.ReactNode;
    loadData?: (dataNode: LegacyDataNode) => Promise<unknown>;
    treeNodeFilterProp?: string;
    treeNodeLabelProp?: string;
    treeDataSimpleMode?: boolean | SimpleModeConfig;
    treeExpandedKeys?: Key[];
    treeDefaultExpandedKeys?: Key[];
    treeLoadedKeys?: Key[];
    treeCheckable?: boolean | React.ReactNode;
    treeCheckStrictly?: boolean;
    showCheckedStrategy?: CheckedStrategy;
    treeDefaultExpandAll?: boolean;
    treeData?: DataNode[];
    treeLine?: boolean;
    treeIcon?: IconType;
    showTreeIcon?: boolean;
    switcherIcon?: IconType;
    treeMotion?: any;
    children?: React.ReactNode;
    filterTreeNode?: boolean | FilterFunc<LegacyDataNode>;
    dropdownPopupAlign?: any;
    onSearch?: (value: string) => void;
    onChange?: (value: ValueType, labelList: React.ReactNode[], extra: ChangeEventExtra) => void;
    onTreeExpand?: (expandedKeys: Key[]) => void;
    onTreeLoad?: (loadedKeys: Key[]) => void;
    /** `searchPlaceholder` has been removed since search box has been merged into input box */
    searchPlaceholder?: React.ReactNode;
}
declare class TreeSelect<ValueType = DefaultValueType> extends React.Component<TreeSelectProps<ValueType>, {}> {
    static TreeNode: React.FC<import("./TreeNode").TreeNodeProps>;
    static SHOW_ALL: typeof SHOW_ALL;
    static SHOW_PARENT: typeof SHOW_PARENT;
    static SHOW_CHILD: typeof SHOW_CHILD;
    selectRef: React.RefObject<RefSelectProps>;
    focus: () => void;
    blur: () => void;
    render(): JSX.Element;
}
export default TreeSelect;
