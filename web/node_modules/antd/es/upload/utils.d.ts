import { RcFile, UploadFile } from './interface';
export declare function T(): boolean;
export declare function fileToObject(file: RcFile): UploadFile;
export declare function getFileItem(file: UploadFile, fileList: UploadFile[]): UploadFile<any>;
export declare function removeFileItem(file: UploadFile, fileList: UploadFile[]): UploadFile<any>[] | null;
export declare const isImageUrl: (file: UploadFile<any>) => boolean;
export declare function previewImage(file: File | Blob): Promise<string>;
