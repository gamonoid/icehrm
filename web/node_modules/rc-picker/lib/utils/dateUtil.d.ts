import { GenerateConfig } from '../generate';
import { NullableDateType, PickerMode } from '../interface';
export declare const WEEK_DAY_COUNT = 7;
export declare function isNullEqual<T>(value1: T, value2: T): boolean | undefined;
export declare function isSameDecade<DateType>(generateConfig: GenerateConfig<DateType>, decade1: NullableDateType<DateType>, decade2: NullableDateType<DateType>): boolean;
export declare function isSameYear<DateType>(generateConfig: GenerateConfig<DateType>, year1: NullableDateType<DateType>, year2: NullableDateType<DateType>): boolean;
export declare function getQuarter<DateType>(generateConfig: GenerateConfig<DateType>, date: DateType): number;
export declare function isSameQuarter<DateType>(generateConfig: GenerateConfig<DateType>, quarter1: NullableDateType<DateType>, quarter2: NullableDateType<DateType>): boolean;
export declare function isSameMonth<DateType>(generateConfig: GenerateConfig<DateType>, month1: NullableDateType<DateType>, month2: NullableDateType<DateType>): boolean;
export declare function isSameDate<DateType>(generateConfig: GenerateConfig<DateType>, date1: NullableDateType<DateType>, date2: NullableDateType<DateType>): boolean;
export declare function isSameTime<DateType>(generateConfig: GenerateConfig<DateType>, time1: NullableDateType<DateType>, time2: NullableDateType<DateType>): boolean;
export declare function isSameWeek<DateType>(generateConfig: GenerateConfig<DateType>, locale: string, date1: NullableDateType<DateType>, date2: NullableDateType<DateType>): boolean;
export declare function isEqual<DateType>(generateConfig: GenerateConfig<DateType>, value1: NullableDateType<DateType>, value2: NullableDateType<DateType>): boolean;
/** Between in date but not equal of date */
export declare function isInRange<DateType>(generateConfig: GenerateConfig<DateType>, startDate: NullableDateType<DateType>, endDate: NullableDateType<DateType>, current: NullableDateType<DateType>): boolean;
export declare function getWeekStartDate<DateType>(locale: string, generateConfig: GenerateConfig<DateType>, value: DateType): DateType;
export declare function getClosingViewDate<DateType>(viewDate: DateType, picker: PickerMode, generateConfig: GenerateConfig<DateType>, offset?: number): DateType;
