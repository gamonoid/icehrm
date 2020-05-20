/// <reference types="react" />
import { Components, RangeList, Locale } from '../interface';
export interface RangesProps {
    prefixCls: string;
    rangeList?: RangeList;
    components?: Components;
    needConfirmButton: boolean;
    onNow?: null | (() => void) | false;
    onOk?: null | (() => void) | false;
    okDisabled?: boolean;
    locale: Locale;
}
export default function getRanges({ prefixCls, rangeList, components, needConfirmButton, onNow, onOk, okDisabled, locale, }: RangesProps): JSX.Element;
