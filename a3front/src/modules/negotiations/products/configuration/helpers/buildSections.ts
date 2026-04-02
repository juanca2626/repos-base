import type {
  MenuSection,
  MenuItem,
} from '@/modules/negotiations/products/configuration/interfaces';
import { ServiceTypeEnum } from '@/modules/negotiations/products/general/enums/service-type.enum';
import { SidebarSectionEnum } from '@/modules/negotiations/products/configuration/enums/sidebar-section.enum';

const baseItems = [
  { id: 'service-details', label: 'Detalles del servicio', completed: false },
  { id: 'configuration', label: 'Configuración', completed: false },
  { id: 'content', label: 'Contenido', completed: false },
  { id: 'pricing-plans', label: 'Planes tarifarios', completed: false },
];

const imageItems = [{ id: 'images', label: 'Imágenes', completed: false }];

const makeSection = (
  title: string,
  key: string,
  items: MenuItem[],
  currentKey?: string
): MenuSection => {
  return {
    title,
    key,
    expanded: true,
    items,
    currentKey,
  };
};

export const getSections = (serviceTypeId: number | null): MenuSection[] => {
  const sections = {
    multiDays: [
      makeSection('Temporada baja', SidebarSectionEnum.LOW_SEASON, [...baseItems]),
      makeSection('Temporada alta', SidebarSectionEnum.HIGH_SEASON, [...imageItems]),
    ],
    trainTicket: [
      makeSection('Tipo de tren', SidebarSectionEnum.TRAIN_TYPE, [
        ...baseItems,
        { id: 'train-type-images', label: 'Imágenes', completed: false },
      ]),
      makeSection('Tipo de tren', SidebarSectionEnum.TRAIN_TYPE_SECOND, [...imageItems]),
    ],
    default: [
      makeSection('Compartido', SidebarSectionEnum.SHARED, [...baseItems]),
      makeSection('Privado', SidebarSectionEnum.PRIVATE, [...imageItems]),
    ],
  };

  if (serviceTypeId === ServiceTypeEnum.MULTIDAYS) return structuredClone(sections.multiDays);

  if (serviceTypeId === ServiceTypeEnum.TRAIN_TICKET) return structuredClone(sections.trainTicket);

  return structuredClone(sections.default);
};
