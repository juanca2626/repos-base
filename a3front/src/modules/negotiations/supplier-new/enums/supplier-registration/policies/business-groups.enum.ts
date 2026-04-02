/**
 * Enumeración de grupos de negocios
 */
export enum BusinessGroupEnum {
  GENERAL = 1,
  FITS = 2,
  GROUPS = 3,
}

/**
 * Estructura de un grupo de negocio
 */
export interface BusinessGroup {
  id: number;
  name: string;
}

/**
 * Lista de grupos de negocios con su id y nombre
 */
export const BUSINESS_GROUPS: BusinessGroup[] = [
  {
    id: BusinessGroupEnum.GENERAL,
    name: 'GENERAL',
  },
  {
    id: BusinessGroupEnum.FITS,
    name: 'FITS',
  },
  {
    id: BusinessGroupEnum.GROUPS,
    name: 'GRUPOS',
  },
];

/**
 * Función para obtener el nombre de un grupo de negocio a partir de su id
 * @param id ID del grupo de negocio
 * @returns Nombre del grupo de negocio o undefined si no existe
 */
export function getBusinessGroupName(id: number): string | undefined {
  const group = BUSINESS_GROUPS.find((group) => group.id === id);
  return group?.name;
}

/**
 * Función para obtener un grupo de negocio a partir de su id
 * @param id ID del grupo de negocio
 * @returns Objeto BusinessGroup o undefined si no existe
 */
export function getBusinessGroup(id: number): BusinessGroup | undefined {
  return BUSINESS_GROUPS.find((group) => group.id === id);
}
