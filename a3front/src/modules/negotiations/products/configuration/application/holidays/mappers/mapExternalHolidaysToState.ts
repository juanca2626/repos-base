import { nanoid } from 'nanoid';
import type { HolidaysResponse } from '@/modules/negotiations/products/configuration/domain/holidays/models/holidaysResponse.model';
import type { HolidayGroup } from '@/modules/negotiations/products/configuration/domain/holidays/models/holiday.model';
import type { HolidayDate } from '@/modules/negotiations/products/configuration/domain/holidays/models/holidayDate.model';
import { HolidayScopeEnum } from '@/modules/negotiations/products/configuration/domain/holidays/enums/holidayScope.enum';
import { HolidayDateTypeEnum } from '@/modules/negotiations/products/configuration/domain/holidays/enums/holidayDateType.enum';
import type {
  HolidaysDTO,
  HolidayGroupDTO,
  HolidayDateDTO,
} from '@/modules/negotiations/products/configuration/infrastructure/holidays/dto/holidays.dto';

function mapHolidayDate(dto: HolidayDateDTO): HolidayDate {
  return {
    externalId: dto.externalId,
    name: dto.name,
    apiType: dto.apiType as HolidayDateTypeEnum,
    apiDate: dto.apiDate,
    apiDateRange: null,
    isActive: dto.isActive,
    isModified: dto.isModified,
    isNewFromApi: dto.isNewFromApi,
    moveInfo: {
      originGroupKey: dto.moveInfo.originGroupKey,
      currentGroupKey: dto.moveInfo.currentGroupKey,
      movedAt: dto.moveInfo.movedAt,
    },
    syncConflict: dto.syncConflict,
    isOrphan: dto.isOrphan,
    expandedDates: dto.expandedDates,
    history: dto.history,
  };
}

function mapHolidayGroup(dto: HolidayGroupDTO): HolidayGroup {
  return {
    uuid: nanoid(),
    key: dto.key as HolidayScopeEnum,
    label: dto.label,
    priority: dto.priority,
    isActive: dto.isActive ?? true,
    dates: dto.dates.map(mapHolidayDate),
  };
}

export function mapExternalHolidaysToState(dto: HolidaysDTO): HolidaysResponse {
  if (!dto) {
    return {
      country: undefined,
      groups: [],
    };
  }

  return {
    country: dto.country,
    groups: dto.groups?.map(mapHolidayGroup) ?? [],
  };
}
