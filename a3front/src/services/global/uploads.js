import request from '../../utils/requestUploads';

export function sendChunk(params) {
  return request({
    method: 'POST',
    url: 'chunk/upload',
    data: params,
  });
}

export function sendFiles(params) {
  return request({
    method: 'POST',
    url: 'upload',
    data: {
      files: params,
    },
  });
}

export function deleteFile(file) {
  return request({
    method: 'DELETE',
    url: 'delete',
    data: {
      file: file,
    },
  });
}
