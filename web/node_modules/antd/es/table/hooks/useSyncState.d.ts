declare type UseSyncStateProps<T> = [() => T, (newValue: T) => void];
export default function useSyncState<T>(filteredKeys: T): UseSyncStateProps<T>;
export {};
