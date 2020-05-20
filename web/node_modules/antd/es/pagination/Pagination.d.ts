import * as React from 'react';
export interface PaginationProps {
    total?: number;
    defaultCurrent?: number;
    disabled?: boolean;
    current?: number;
    defaultPageSize?: number;
    pageSize?: number;
    onChange?: (page: number, pageSize?: number) => void;
    hideOnSinglePage?: boolean;
    showSizeChanger?: boolean;
    pageSizeOptions?: string[];
    onShowSizeChange?: (current: number, size: number) => void;
    showQuickJumper?: boolean | {
        goButton?: React.ReactNode;
    };
    showTitle?: boolean;
    showTotal?: (total: number, range: [number, number]) => React.ReactNode;
    size?: 'default' | 'small';
    responsive?: boolean;
    simple?: boolean;
    style?: React.CSSProperties;
    locale?: Object;
    className?: string;
    prefixCls?: string;
    selectPrefixCls?: string;
    itemRender?: (page: number, type: 'page' | 'prev' | 'next' | 'jump-prev' | 'jump-next', originalElement: React.ReactElement<HTMLElement>) => React.ReactNode;
    role?: string;
    showLessItems?: boolean;
}
export declare type PaginationPosition = 'top' | 'bottom' | 'both' | 'topLeft' | 'topCenter' | 'topRight' | 'bottomLeft' | 'bottomCenter' | 'bottomRight';
export interface PaginationConfig extends PaginationProps {
    position?: PaginationPosition[] | PaginationPosition;
}
export declare type PaginationLocale = any;
export default class Pagination extends React.Component<PaginationProps, {}> {
    private token;
    private inferredSmall;
    componentDidMount(): void;
    componentWillUnmount(): void;
    getIconsProps: (prefixCls: string, direction: "ltr" | "rtl" | undefined) => {
        prevIcon: JSX.Element;
        nextIcon: JSX.Element;
        jumpPrevIcon: JSX.Element;
        jumpNextIcon: JSX.Element;
    };
    renderPagination: (contextLocale: any) => JSX.Element;
    render(): JSX.Element;
}
