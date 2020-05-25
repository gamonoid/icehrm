import { FlattenDataNode } from '../interface';
/**
 * Return cached Key Value map with DataNode.
 * Only re-calculate when `flattenOptions` changed.
 */
export default function useKeyValueMap(flattenOptions: FlattenDataNode[]): Map<string | number, FlattenDataNode>[];
