import * as React from 'react';
import BreadcrumbItem from './BreadcrumbItem';
import BreadcrumbSeparator from './BreadcrumbSeparator';
import { ConfigConsumerProps } from '../config-provider';
import { Omit } from '../_util/type';
export interface Route {
    path: string;
    breadcrumbName: string;
    children?: Omit<Route, 'children'>[];
}
export interface BreadcrumbProps {
    prefixCls?: string;
    routes?: Route[];
    params?: any;
    separator?: React.ReactNode;
    itemRender?: (route: Route, params: any, routes: Array<Route>, paths: Array<string>) => React.ReactNode;
    style?: React.CSSProperties;
    className?: string;
}
export default class Breadcrumb extends React.Component<BreadcrumbProps, any> {
    static Item: typeof BreadcrumbItem;
    static Separator: typeof BreadcrumbSeparator;
    static defaultProps: {
        separator: string;
    };
    getPath: (path: string, params: any) => string;
    addChildPath: (paths: string[], childPath: string | undefined, params: any) => string[];
    genForRoutes: ({ routes, params, separator, itemRender, }: BreadcrumbProps) => JSX.Element[];
    renderBreadcrumb: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
