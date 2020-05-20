import * as React from 'react';
import RadioGroup from './group';
import RadioButton from './radioButton';
import { RadioProps, RadioChangeEvent } from './interface';
import { ConfigConsumerProps } from '../config-provider';
export default class Radio extends React.PureComponent<RadioProps, {}> {
    static Group: typeof RadioGroup;
    static Button: typeof RadioButton;
    static defaultProps: {
        type: string;
    };
    static contextType: React.Context<import("./interface").RadioGroupContextProps | null>;
    private rcCheckbox;
    saveCheckbox: (node: any) => void;
    onChange: (e: RadioChangeEvent) => void;
    focus(): void;
    blur(): void;
    renderRadio: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
