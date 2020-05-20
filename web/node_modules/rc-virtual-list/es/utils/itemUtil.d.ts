/**
 * Our algorithm have additional one ghost item
 * whose index as `data.length` to simplify the calculation
 */
export declare const GHOST_ITEM_KEY = "__rc_ghost_item__";
export declare type Key = string | number;
/**
 * Safari has the elasticity effect which provides negative `scrollTop` value.
 * We should ignore it since will make scroll animation shake.
 */
export declare function alignScrollTop(scrollTop: number, scrollRange: number): number;
export declare function getScrollPercentage({ scrollTop, scrollHeight, clientHeight, }: {
    scrollTop: number;
    scrollHeight: number;
    clientHeight: number;
}): number;
export declare function getElementScrollPercentage(element: HTMLElement | null): number;
/**
 * Get node `offsetHeight`. We prefer node is a dom element directly.
 * But if not provided, downgrade to `findDOMNode` to get the real dom element.
 */
export declare function getNodeHeight(node: HTMLElement): number;
/**
 * Get display items start, end, located item index. This is pure math calculation
 */
export declare function getRangeIndex(scrollPtg: number, itemCount: number, visibleCount: number): {
    itemIndex: number;
    itemOffsetPtg: number;
    startIndex: number;
    endIndex: number;
};
interface ItemTopConfig {
    itemIndex: number;
    itemElementHeights: {
        [key: string]: number;
    };
    itemOffsetPtg: number;
    scrollTop: number;
    scrollPtg: number;
    clientHeight: number;
    getItemKey: (index: number) => Key;
}
/**
 * Calculate the located item related top with current window height
 */
export declare function getItemRelativeTop({ itemIndex, itemOffsetPtg, itemElementHeights, scrollPtg, clientHeight, getItemKey, }: Omit<ItemTopConfig, 'scrollTop'>): number;
/**
 * Calculate the located item absolute top with whole scroll height
 */
export declare function getItemAbsoluteTop({ scrollTop, ...rest }: ItemTopConfig): number;
interface CompareItemConfig {
    locatedItemRelativeTop: number;
    locatedItemIndex: number;
    compareItemIndex: number;
    getItemKey: (index: number) => Key;
    startIndex: number;
    endIndex: number;
    itemElementHeights: {
        [key: string]: number;
    };
}
export declare function getCompareItemRelativeTop({ locatedItemRelativeTop, locatedItemIndex, compareItemIndex, startIndex, endIndex, getItemKey, itemElementHeights, }: CompareItemConfig): number;
export declare function requireVirtual(height: number, itemHeight: number, count: number, virtual: boolean): boolean;
export {};
