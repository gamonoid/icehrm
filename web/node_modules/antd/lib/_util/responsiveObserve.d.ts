export declare type Breakpoint = 'xxl' | 'xl' | 'lg' | 'md' | 'sm' | 'xs';
export declare type BreakpointMap = Partial<Record<Breakpoint, string>>;
export declare type ScreenMap = Partial<Record<Breakpoint, boolean>>;
export declare const responsiveArray: Breakpoint[];
export declare const responsiveMap: BreakpointMap;
declare type SubscribeFunc = (screens: ScreenMap) => void;
declare const responsiveObserve: {
    matchHandlers: {};
    dispatch(pointMap: Partial<Record<Breakpoint, boolean>>): boolean;
    subscribe(func: SubscribeFunc): string;
    unsubscribe(token: string): void;
    unregister(): void;
    register(): void;
};
export default responsiveObserve;
