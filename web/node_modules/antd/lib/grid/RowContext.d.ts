import { Context } from 'react';
export interface RowContextState {
    gutter?: [number, number];
}
declare const RowContext: Context<RowContextState>;
export default RowContext;
