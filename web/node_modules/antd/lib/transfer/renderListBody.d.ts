/// <reference types="react" />
import { ElementOf, Omit } from '../_util/type';
import { TransferItem } from '.';
import { TransferListProps, RenderedItem } from './list';
export declare const OmitProps: ["handleFilter", "handleClear", "checkedKeys"];
export declare type OmitProp = ElementOf<typeof OmitProps>;
declare type PartialTransferListProps = Omit<TransferListProps, OmitProp>;
export interface TransferListBodyProps extends PartialTransferListProps {
    filteredItems: TransferItem[];
    filteredRenderItems: RenderedItem[];
    selectedKeys: string[];
}
declare const ListBodyWrapper: (props: TransferListBodyProps) => JSX.Element;
export default ListBodyWrapper;
