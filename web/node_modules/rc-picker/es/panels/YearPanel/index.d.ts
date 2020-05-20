/// <reference types="react" />
import { PanelSharedProps, PanelMode } from '../../interface';
export interface YearPanelProps<DateType> extends PanelSharedProps<DateType> {
    sourceMode: PanelMode;
}
export declare const YEAR_DECADE_COUNT = 10;
declare function YearPanel<DateType>(props: YearPanelProps<DateType>): JSX.Element;
export default YearPanel;
