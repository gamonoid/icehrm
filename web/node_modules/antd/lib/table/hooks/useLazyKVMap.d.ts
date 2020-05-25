import * as React from 'react';
import { GetRowKey } from '../interface';
export default function useLazyKVMap<RecordType>(data: RecordType[], childrenColumnName: string, getRowKey: GetRowKey<RecordType>): ((key: React.ReactText) => RecordType)[];
