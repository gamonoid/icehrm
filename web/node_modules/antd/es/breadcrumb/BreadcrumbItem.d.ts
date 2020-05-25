import * as React from 'react';
import { DropDownProps } from '../dropdown/dropdown';
import { ConfigConsumerProps } from '../config-provider';
export interface BreadcrumbItemProps {
    prefixCls?: string;
    separator?: React.ReactNode;
    href?: string;
    overlay?: DropDownProps['overlay'];
    dropdownProps?: DropDownProps;
    onClick?: React.MouseEventHandler<HTMLAnchorElement | HTMLSpanElement>;
}
export default class BreadcrumbItem extends React.Component<BreadcrumbItemProps, any> {
    static __ANT_BREADCRUMB_ITEM: boolean;
    static defaultProps: {
        separator: string;
    };
    renderBreadcrumbItem: ({ getPrefixCls }: ConfigConsumerProps) => JSX.Element | null;
    /**
     * if overlay is have
     * Wrap a DropDown
     */
    renderBreadcrumbNode: (breadcrumbItem: React.ReactNode, prefixCls: string) => {} | null | undefined;
    render(): JSX.Element;
}
