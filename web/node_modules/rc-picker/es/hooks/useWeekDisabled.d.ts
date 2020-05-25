import { GenerateConfig } from '../generate';
import { Locale } from '../interface';
export default function useWeekDisabled<DateType>({ disabledDate, locale, generateConfig, }: {
    disabledDate: (date: DateType) => boolean;
    locale: Locale;
    generateConfig: GenerateConfig<DateType>;
}): [(date: DateType) => boolean];
