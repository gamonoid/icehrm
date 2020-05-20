/// <reference types="react" />
import { PanelSharedProps } from '../../interface';
export interface QuarterPanelProps<DateType> extends PanelSharedProps<DateType> {
}
declare function QuarterPanel<DateType>(props: QuarterPanelProps<DateType>): JSX.Element;
export default QuarterPanel;
