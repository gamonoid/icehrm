export declare type Updater<State> = (prev: State) => State;
export declare function useFrameState<State>(defaultState: State): [State, (updater: Updater<State>) => void];
/** Lock frame, when frame pass reset the lock. */
export declare function useTimeoutLock<State>(defaultState?: State): [(state: State) => void, () => State];
