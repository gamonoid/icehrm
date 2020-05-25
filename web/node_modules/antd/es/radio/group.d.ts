import * as React from 'react';
import { RadioGroupProps, RadioGroupState, RadioChangeEvent, RadioGroupButtonStyle } from './interface';
import { ConfigConsumerProps } from '../config-provider';
declare class RadioGroup extends React.PureComponent<RadioGroupProps, RadioGroupState> {
    static defaultProps: {
        buttonStyle: RadioGroupButtonStyle;
    };
    static getDerivedStateFromProps(nextProps: RadioGroupProps, prevState: RadioGroupState): Partial<RadioGroupState>;
    constructor(props: RadioGroupProps);
    onRadioChange: (ev: RadioChangeEvent) => void;
    renderGroup: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
export default RadioGroup;
