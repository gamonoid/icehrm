import * as React from 'react';
import { OptionsType as SelectOptionsType, RenderNode } from './interface';
import { RawValueType, FlattenOptionsType } from './interface/generator';
export interface OptionListProps<OptionsType extends object[]> {
    prefixCls: string;
    id: string;
    options: OptionsType;
    flattenOptions: FlattenOptionsType<OptionsType>;
    height: number;
    itemHeight: number;
    values: Set<RawValueType>;
    multiple: boolean;
    open: boolean;
    defaultActiveFirstOption?: boolean;
    notFoundContent?: React.ReactNode;
    menuItemSelectedIcon?: RenderNode;
    childrenAsData: boolean;
    searchValue: string;
    virtual: boolean;
    onSelect: (value: RawValueType, option: {
        selected: boolean;
    }) => void;
    onToggleOpen: (open?: boolean) => void;
    /** Tell Select that some value is now active to make accessibility work */
    onActiveValue: (value: RawValueType, index: number) => void;
    onScroll: React.UIEventHandler<HTMLDivElement>;
}
export interface RefOptionListProps {
    onKeyDown: React.KeyboardEventHandler;
    onKeyUp: React.KeyboardEventHandler;
    scrollTo?: (index: number) => void;
}
declare const RefOptionList: React.ForwardRefExoticComponent<OptionListProps<SelectOptionsType> & React.RefAttributes<RefOptionListProps>>;
export default RefOptionList;
