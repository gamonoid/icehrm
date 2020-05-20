import * as React from 'react';
import { InputProps } from './Input';
import { SizeType } from '../config-provider/SizeContext';
declare const ClearableInputType: ["text", "input"];
export declare function hasPrefixSuffix(props: InputProps | ClearableInputProps): boolean;
/**
 * This basic props required for input and textarea.
 */
interface BasicProps {
    prefixCls: string;
    inputType: typeof ClearableInputType[number];
    value?: any;
    allowClear?: boolean;
    element: React.ReactElement<any>;
    handleReset: (event: React.MouseEvent<HTMLElement, MouseEvent>) => void;
    className?: string;
    style?: object;
    disabled?: boolean;
    direction?: any;
    focused?: boolean;
    readOnly?: boolean;
}
/**
 * This props only for input.
 */
interface ClearableInputProps extends BasicProps {
    size?: SizeType;
    suffix?: React.ReactNode;
    prefix?: React.ReactNode;
    addonBefore?: React.ReactNode;
    addonAfter?: React.ReactNode;
    triggerFocus: () => void;
}
declare class ClearableLabeledInput extends React.Component<ClearableInputProps> {
    /** @private Do not use out of this class. We do not promise this is always keep. */
    private containerRef;
    onInputMouseUp: React.MouseEventHandler;
    renderClearIcon(prefixCls: string): JSX.Element | null;
    renderSuffix(prefixCls: string): JSX.Element | null;
    renderLabeledIcon(prefixCls: string, element: React.ReactElement<any>): JSX.Element;
    renderInputWithLabel(prefixCls: string, labeledElement: React.ReactElement<any>): JSX.Element;
    renderTextAreaWithClearIcon(prefixCls: string, element: React.ReactElement<any>): JSX.Element;
    renderClearableLabeledInput(): JSX.Element;
    render(): JSX.Element;
}
export default ClearableLabeledInput;
