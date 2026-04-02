export const mapVariationStatus = (status: string) => {
  switch (status) {
    case 'COMPLETED':
      return { label: 'Completado', class: 'completed' };

    case 'IN_PROGRESS':
      return { label: 'En progreso', class: 'in-progress' };

    case 'NOT_STARTED':
    default:
      return { label: 'No iniciado', class: 'not-started' };
  }
};
