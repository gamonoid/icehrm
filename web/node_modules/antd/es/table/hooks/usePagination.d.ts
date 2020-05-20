import { PaginationConfig } from '../../pagination';
import { TablePaginationConfig } from '../interface';
export declare const DEFAULT_PAGE_SIZE = 10;
export declare function getPaginationParam(pagination: PaginationConfig | boolean | undefined, mergedPagination: PaginationConfig): any;
export default function usePagination(total: number, pagination: TablePaginationConfig | false | undefined, onChange: (current: number, pageSize: number) => void): [TablePaginationConfig, () => void];
