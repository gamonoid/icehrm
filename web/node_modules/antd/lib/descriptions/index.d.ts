import * as React from 'react';
import { Breakpoint } from '../_util/responsiveObserve';
export interface DescriptionsProps {
    prefixCls?: string;
    className?: string;
    style?: React.CSSProperties;
    bordered?: boolean;
    size?: 'middle' | 'small' | 'default';
    children?: React.ReactNode;
    title?: React.ReactNode;
    column?: number | Partial<Record<Breakpoint, number>>;
    layout?: 'horizontal' | 'vertical';
    colon?: boolean;
}
declare function Descriptions({ prefixCls: customizePrefixCls, title, column, colon, bordered, layout, children, className, style, size, }: DescriptionsProps): JSX.Element;
declare namespace Descriptions {
    var Item: React.FC<import("./Item").DescriptionsItemProps>;
}
export default Descriptions;
