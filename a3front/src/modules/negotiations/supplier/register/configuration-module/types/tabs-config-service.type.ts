import type { SupplierSubClassifications } from '@/modules/negotiations/constants';
import type { TabKeyEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/tab-key.enum';
import type { TabOrderMapping } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export type TabOrder = Partial<Record<TabKeyEnum, number>>;

export type TabsOrderMapping = Partial<Record<SupplierSubClassifications, TabOrder>>;

export type TabOptions = Partial<Record<SupplierSubClassifications, TabOrderMapping[]>>;
