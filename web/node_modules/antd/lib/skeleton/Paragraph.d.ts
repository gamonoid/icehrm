/// <reference types="react" />
declare type widthUnit = number | string;
export interface SkeletonParagraphProps {
    prefixCls?: string;
    className?: string;
    style?: object;
    width?: widthUnit | Array<widthUnit>;
    rows?: number;
}
declare const Paragraph: (props: SkeletonParagraphProps) => JSX.Element;
export default Paragraph;
