import * as React from 'react';
interface IndentProps {
    prefixCls: string;
    level: number;
    isStart: boolean[];
    isEnd: boolean[];
}
declare const Indent: React.FC<IndentProps>;
export default Indent;
