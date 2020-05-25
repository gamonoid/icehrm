import { RangeValue, PickerMode, Locale } from '../interface';
import { GenerateConfig } from '../generate';
export default function useRangeDisabled<DateType>({ picker, locale, selectedValue, disabledDate, disabled, generateConfig, }: {
    picker: PickerMode;
    selectedValue: RangeValue<DateType>;
    disabledDate?: (date: DateType) => boolean;
    disabled: [boolean, boolean];
    locale: Locale;
    generateConfig: GenerateConfig<DateType>;
}): ((date: DateType) => boolean)[];
