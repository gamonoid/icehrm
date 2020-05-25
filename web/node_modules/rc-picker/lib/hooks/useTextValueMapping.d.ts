export default function useTextValueMapping<ValueType>({ valueTexts, onTextChange, }: {
    /** Must useMemo, to assume that `valueTexts` only match on the first change */
    valueTexts: string[];
    onTextChange: (text: string) => void;
}): [string, (text: string) => void, () => void];
