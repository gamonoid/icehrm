import * as React from 'react';
import { TreeNodeProps } from './TreeNode';
import { FlattenNode } from './interface';
import { TreeNodeRequiredProps } from './utils/treeUtil';
interface MotionTreeNodeProps extends Omit<TreeNodeProps, 'domRef'> {
    active: boolean;
    motion?: any;
    motionNodes?: FlattenNode[];
    onMotionEnd: () => void;
    motionType?: 'show' | 'hide';
    treeNodeRequiredProps: TreeNodeRequiredProps;
}
declare const RefMotionTreeNode: React.ForwardRefExoticComponent<MotionTreeNodeProps & React.RefAttributes<any>>;
export default RefMotionTreeNode;
