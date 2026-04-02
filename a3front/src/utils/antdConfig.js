export const globalGetPopupContainer = (triggerNode) => {
  if (!triggerNode) return document.body;

  // Obtener el tipo de componente del trigger
  const trigger = triggerNode;

  // Verificar si tiene clases relacionadas con tooltip o popover
  let node = trigger;
  let depth = 0;
  const maxDepth = 10;

  while (node && depth < maxDepth) {
    const className = node.className?.toString() || '';
    const tagName = node.tagName?.toLowerCase() || '';

    // Si es un SVG o está dentro de un label, es probablemente un tooltip
    if (tagName === 'svg' || tagName === 'path' || className.includes('tooltip')) {
      return document.body;
    }

    // Si es un input de select, usar parentNode
    if (className.includes('ant-select') || className.includes('ant-picker')) {
      return trigger.parentNode || document.body;
    }

    node = node.parentNode;
    depth++;
  }

  // Por defecto, usar parentNode para selects y body para el resto
  const className = trigger.className?.toString() || '';
  if (className.includes('ant-select') || className.includes('ant-picker')) {
    return trigger.parentNode || document.body;
  }

  return document.body;
};

/**
 * Props comunes para todos los componentes select/picker de Ant Design
 */
export const selectDropdownProps = {
  getPopupContainer: globalGetPopupContainer,
};
