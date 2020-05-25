import React from 'react';
import { LabelValueType, RawValueType, CustomTagProps } from '../interface/generator';
import { RenderNode } from '../interface';
import { InnerSelectorProps } from '.';
interface SelectorProps extends InnerSelectorProps {
    removeIcon?: RenderNode;
    maxTagCount?: number;
    maxTagTextLength?: number;
    maxTagPlaceholder?: React.ReactNode | ((omittedValues: LabelValueType[]) => React.ReactNode);
    tokenSeparators?: string[];
    tagRender?: (props: CustomTagProps) => React.ReactElement;
    choiceTransitionName?: string;
    onSelect: (value: RawValueType, option: {
        selected: boolean;
    }) => void;
}
declare const SelectSelector: React.FC<SelectorProps>;
export default SelectSelector;
