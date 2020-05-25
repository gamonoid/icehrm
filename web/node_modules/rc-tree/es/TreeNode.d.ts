import * as React from 'react';
import PropTypes from 'prop-types';
import { TreeContextProps } from './contextTypes';
import { IconType, Key, DataNode } from './interface';
export interface TreeNodeProps {
    eventKey?: Key;
    prefixCls?: string;
    className?: string;
    style?: React.CSSProperties;
    expanded?: boolean;
    selected?: boolean;
    checked?: boolean;
    loaded?: boolean;
    loading?: boolean;
    halfChecked?: boolean;
    title?: React.ReactNode | ((data: DataNode) => React.ReactNode);
    dragOver?: boolean;
    dragOverGapTop?: boolean;
    dragOverGapBottom?: boolean;
    pos?: string;
    domRef?: React.Ref<HTMLDivElement>;
    /** New added in Tree for easy data access */
    data?: DataNode;
    isStart?: boolean[];
    isEnd?: boolean[];
    active?: boolean;
    onMouseMove?: React.MouseEventHandler<HTMLDivElement>;
    isLeaf?: boolean;
    checkable?: boolean;
    selectable?: boolean;
    disabled?: boolean;
    disableCheckbox?: boolean;
    icon?: IconType;
    switcherIcon?: IconType;
    children?: React.ReactNode;
}
export interface InternalTreeNodeProps extends TreeNodeProps {
    context?: TreeContextProps;
}
export interface TreeNodeState {
    dragNodeHighlight: boolean;
}
declare class InternalTreeNode extends React.Component<InternalTreeNodeProps, TreeNodeState> {
    static propTypes: {
        prefixCls: PropTypes.Requireable<string>;
        className: PropTypes.Requireable<string>;
        style: PropTypes.Requireable<object>;
        onSelect: PropTypes.Requireable<(...args: any[]) => any>;
        eventKey: PropTypes.Requireable<string | number>;
        expanded: PropTypes.Requireable<boolean>;
        selected: PropTypes.Requireable<boolean>;
        checked: PropTypes.Requireable<boolean>;
        loaded: PropTypes.Requireable<boolean>;
        loading: PropTypes.Requireable<boolean>;
        halfChecked: PropTypes.Requireable<boolean>;
        title: PropTypes.Requireable<PropTypes.ReactNodeLike>;
        dragOver: PropTypes.Requireable<boolean>;
        dragOverGapTop: PropTypes.Requireable<boolean>;
        dragOverGapBottom: PropTypes.Requireable<boolean>;
        pos: PropTypes.Requireable<string>;
        isLeaf: PropTypes.Requireable<boolean>;
        checkable: PropTypes.Requireable<boolean>;
        selectable: PropTypes.Requireable<boolean>;
        disabled: PropTypes.Requireable<boolean>;
        disableCheckbox: PropTypes.Requireable<boolean>;
        icon: PropTypes.Requireable<PropTypes.ReactNodeLike>;
        switcherIcon: PropTypes.Requireable<PropTypes.ReactNodeLike>;
    };
    state: {
        dragNodeHighlight: boolean;
    };
    selectHandle: HTMLSpanElement;
    componentDidMount(): void;
    componentDidUpdate(): void;
    onSelectorClick: (e: any) => void;
    onSelectorDoubleClick: (e: any) => void;
    onSelect: (e: any) => void;
    onCheck: (e: any) => void;
    onMouseEnter: (e: any) => void;
    onMouseLeave: (e: any) => void;
    onContextMenu: (e: any) => void;
    onDragStart: (e: any) => void;
    onDragEnter: (e: any) => void;
    onDragOver: (e: any) => void;
    onDragLeave: (e: any) => void;
    onDragEnd: (e: any) => void;
    onDrop: (e: any) => void;
    onExpand: React.MouseEventHandler<HTMLDivElement>;
    setSelectHandle: (node: any) => void;
    getNodeState: () => "open" | "close";
    hasChildren: () => boolean;
    isLeaf: () => boolean;
    isDisabled: () => boolean;
    isCheckable: () => {};
    syncLoadData: (props: any) => void;
    isSelectable(): boolean;
    renderSwitcher: () => JSX.Element;
    renderCheckbox: () => JSX.Element;
    renderIcon: () => JSX.Element;
    renderSelector: () => JSX.Element;
    render(): JSX.Element;
}
declare const ContextTreeNode: React.FC<TreeNodeProps>;
export { InternalTreeNode };
export default ContextTreeNode;
