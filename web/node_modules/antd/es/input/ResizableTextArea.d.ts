import * as React from 'react';
import { TextAreaProps } from './TextArea';
declare const RESIZE_STATUS_NONE = 0;
declare const RESIZE_STATUS_RESIZING = 1;
declare const RESIZE_STATUS_RESIZED = 2;
export interface AutoSizeType {
    minRows?: number;
    maxRows?: number;
}
export interface TextAreaState {
    textareaStyles?: React.CSSProperties;
    /** We need add process style to disable scroll first and then add back to avoid unexpected scrollbar  */
    resizeStatus?: typeof RESIZE_STATUS_NONE | typeof RESIZE_STATUS_RESIZING | typeof RESIZE_STATUS_RESIZED;
}
declare class ResizableTextArea extends React.Component<TextAreaProps, TextAreaState> {
    nextFrameActionId: number;
    resizeFrameId: number;
    constructor(props: TextAreaProps);
    textArea: HTMLTextAreaElement;
    saveTextArea: (textArea: HTMLTextAreaElement) => void;
    componentDidMount(): void;
    componentDidUpdate(prevProps: TextAreaProps): void;
    handleResize: (size: {
        width: number;
        height: number;
    }) => void;
    resizeOnNextFrame: () => void;
    resizeTextarea: () => void;
    componentWillUnmount(): void;
    fixFirefoxAutoScroll(): void;
    renderTextArea: () => JSX.Element;
    render(): JSX.Element;
}
export default ResizableTextArea;
