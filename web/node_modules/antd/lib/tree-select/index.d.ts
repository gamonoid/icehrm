import * as React from 'react';
import RcTreeSelect, { TreeNode, SHOW_ALL, SHOW_PARENT, SHOW_CHILD, TreeSelectProps as RcTreeSelectProps } from 'rc-tree-select';
import { ConfigConsumerProps } from '../config-provider';
import { SizeType } from '../config-provider/SizeContext';
declare type RawValue = string | number;
export interface LabeledValue {
    key?: string;
    value: RawValue;
    label: React.ReactNode;
}
export declare type SelectValue = RawValue | RawValue[] | LabeledValue | LabeledValue[];
export interface TreeSelectProps<T> extends Omit<RcTreeSelectProps<T>, 'showTreeIcon' | 'treeMotion' | 'inputIcon' | 'mode' | 'getInputElement' | 'backfill'> {
    suffixIcon?: React.ReactNode;
    size?: SizeType;
    bordered?: boolean;
}
declare class TreeSelect<T> extends React.Component<TreeSelectProps<T>, {}> {
    static TreeNode: React.FC<import("rc-tree-select/lib/TreeNode").TreeNodeProps>;
    static SHOW_ALL: typeof SHOW_ALL;
    static SHOW_PARENT: typeof SHOW_PARENT;
    static SHOW_CHILD: typeof SHOW_CHILD;
    static defaultProps: {
        transitionName: string;
        choiceTransitionName: string;
        bordered: boolean;
    };
    selectRef: React.RefObject<RcTreeSelect<import("rc-tree-select/lib/interface").DefaultValueType>>;
    constructor(props: TreeSelectProps<T>);
    focus(): void;
    blur(): void;
    renderTreeSelect: ({ getPopupContainer: getContextPopupContainer, getPrefixCls, renderEmpty, direction, }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
export { TreeNode };
export default TreeSelect;
