/**
 * Legacy code. Should avoid to use if you are new to import these code.
 */
import React from 'react';
import { TreeNodeProps } from './TreeNode';
import { NodeElement, Key, DataNode, DataEntity, NodeInstance } from './interface';
import { TreeProps } from './Tree';
export declare function arrDel(list: Key[], value: Key): (string | number)[];
export declare function arrAdd(list: Key[], value: Key): (string | number)[];
export declare function posToArr(pos: string): string[];
export declare function getPosition(level: string | number, index: number): string;
export declare function isTreeNode(node: NodeElement): boolean;
export declare function getDragNodesKeys(dragNodeKey: Key, keyEntities: Record<Key, DataEntity>): Key[];
export declare function calcDropPosition(event: React.MouseEvent, treeNode: NodeInstance): 1 | 0 | -1;
/**
 * Return selectedKeys according with multiple prop
 * @param selectedKeys
 * @param props
 * @returns [string]
 */
export declare function calcSelectedKeys(selectedKeys: Key[], props: TreeProps): (string | number)[];
export declare function convertDataToTree(treeData: DataNode[], processor?: {
    processProps: (prop: DataNode) => any;
}): NodeElement[];
/**
 * Parse `checkedKeys` to { checkedKeys, halfCheckedKeys } style
 */
export declare function parseCheckedKeys(keys: Key[] | {
    checked: Key[];
    halfChecked: Key[];
}): any;
/**
 * If user use `autoExpandParent` we should get the list of parent node
 * @param keyList
 * @param keyEntities
 */
export declare function conductExpandParent(keyList: Key[], keyEntities: Record<Key, DataEntity>): string[];
/**
 * Returns only the data- and aria- key/value pairs
 */
export declare function getDataAndAria(props: Partial<TreeProps | TreeNodeProps>): Record<string, string>;
