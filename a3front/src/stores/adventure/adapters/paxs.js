export const createPaxAdapter = (file, pax) => {
  const name = pax.fullName || pax.nombre || '';
  const document = pax.documentNumber || pax.nrodoc || '';
  const genre = pax.sex || pax.sexo || '';
  const id = pax.id || pax.nrosec || '';

  return {
    _id: pax._id ?? '',
    file: file,
    id,
    name: name,
    document: document,
    genre: genre,
    fullName: name,
    documentNumber: document,
    sex: genre,
    selected: false,
  };
};
