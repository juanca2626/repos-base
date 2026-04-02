const commonImageTypes = [
  'image/jpeg',
  'image/png',
  'image/gif',
  'image/webp',
  'image/svg+xml',
  'image/bmp',
  'image/tiff',
];

export const allowedFileTypes = ['application/pdf', ...commonImageTypes];

export const allowedPhotoTypes = [...commonImageTypes];
