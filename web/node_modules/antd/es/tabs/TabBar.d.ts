import * as React from 'react';
import { TabsProps } from './index';
import { ConfigConsumerProps } from '../config-provider';
export default class TabBar extends React.Component<TabsProps> {
    static defaultProps: {
        animated: boolean;
        type: "line" | "card" | "editable-card" | undefined;
    };
    renderTabBar: ({ direction }: ConfigConsumerProps) => React.ReactElement<any, string | ((props: any) => React.ReactElement<any, string | any | (new (props: any) => React.Component<any, any, any>)> | null) | (new (props: any) => React.Component<any, any, any>)>;
    render(): JSX.Element;
}
