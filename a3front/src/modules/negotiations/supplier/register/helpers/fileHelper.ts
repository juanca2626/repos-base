const byteToKb = 1024;

export const formatFileSize = (size: number): string => {
  if (size < byteToKb) return `${size.toFixed(1)} B`;
  if (size < byteToKb * byteToKb) return `${(size / byteToKb).toFixed(1)} KB`;
  return `${(size / (byteToKb * byteToKb)).toFixed(1)} MB`;
};
