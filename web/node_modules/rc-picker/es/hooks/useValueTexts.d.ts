import { GenerateConfig } from '../generate';
import { Locale } from '../interface';
interface ValueTextConfig<DateType> {
    formatList: string[];
    generateConfig: GenerateConfig<DateType>;
    locale: Locale;
}
export default function useValueTexts<DateType>(value: DateType | null, { formatList, generateConfig, locale }: ValueTextConfig<DateType>): string[];
export {};
