export const createGuideAdapter = (guide) => {
  return {
    ...guide,
    razon: guide.descri,
    code: guide.codigo ?? '',
    type: guide.descla ?? '',
    typeDescription: guide.descla ?? '',
    selected: false,
    isPermanent: guide.numera === 'L',
  };
};
