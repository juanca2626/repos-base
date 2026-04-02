import request from '../../utils/requestAdventure';

export function fetchManifestos(params) {
  return request({
    method: 'GET',
    url: `/externals/ifx/paxs-manifest`,
    params,
  });
}
