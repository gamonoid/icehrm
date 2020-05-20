import * as React from 'react';
import { TypographyProps } from './Typography';
export declare type BaseType = 'secondary' | 'danger' | 'warning';
interface CopyConfig {
    text?: string;
    onCopy?: () => void;
}
interface EditConfig {
    editing?: boolean;
    onStart?: () => void;
    onChange?: (value: string) => void;
}
interface EllipsisConfig {
    rows?: number;
    expandable?: boolean;
    suffix?: string;
    onExpand?: React.MouseEventHandler<HTMLElement>;
}
export interface BlockProps extends TypographyProps {
    title?: string;
    editable?: boolean | EditConfig;
    copyable?: boolean | CopyConfig;
    type?: BaseType;
    disabled?: boolean;
    ellipsis?: boolean | EllipsisConfig;
    code?: boolean;
    mark?: boolean;
    underline?: boolean;
    delete?: boolean;
    strong?: boolean;
}
interface InternalBlockProps extends BlockProps {
    component: string;
}
declare const _default: React.FC<InternalBlockProps>;
export default _default;
