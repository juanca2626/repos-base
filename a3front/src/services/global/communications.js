import request from '../../utils/request';

export function viewCommunication(object_id, params, type, action) {
  return request({
    method: 'POST',
    url: `files/${object_id}/communication-${type}-${action}`,
    data: params,
  });
}
