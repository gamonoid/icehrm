import * as React from 'react';
export interface LocaleReceiverProps {
    componentName?: string;
    defaultLocale?: object | Function;
    children: (locale: object, localeCode?: string, fullLocale?: object) => React.ReactNode;
}
interface LocaleInterface {
    [key: string]: any;
}
export interface LocaleReceiverContext {
    antLocale?: LocaleInterface;
}
export default class LocaleReceiver extends React.Component<LocaleReceiverProps> {
    static defaultProps: {
        componentName: string;
    };
    static contextType: React.Context<(Partial<import(".").Locale> & {
        exist?: boolean | undefined;
    }) | undefined>;
    getLocale(): any;
    getLocaleCode(): any;
    render(): React.ReactNode;
}
export {};
