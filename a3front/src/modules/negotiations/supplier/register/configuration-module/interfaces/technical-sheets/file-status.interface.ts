export interface FileUploadData {
  isFileUploaded: boolean;
  uploading?: boolean;
  progress?: number;
  uploadedFile?: File | null;
  uploadedSize?: number;
  fileSize: number;
  uploadSpeed?: number; // En KB/sec
  startTime?: number;
  filename?: string;
  documentId?: string;
  uploadInterval: ReturnType<typeof setInterval> | null;
}
