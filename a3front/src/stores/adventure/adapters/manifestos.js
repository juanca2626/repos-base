import dayjs from 'dayjs';

export const createManifestosAdapter = (manifestos) => ({
  ...manifestos,
  fecin: manifestos.fecin ? dayjs(manifestos.fecin).format('DD/MM/YYYY') : '',
  fecout: manifestos.fecout ? dayjs(manifestos.fecout).format('DD/MM/YYYY') : '',
});
