export const createCategoryAdapter = (category) => ({
  ...category,
  label: `${category.name}`,
  value: `${category._id}`,
});
