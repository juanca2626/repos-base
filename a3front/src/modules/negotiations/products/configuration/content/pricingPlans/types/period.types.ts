export type PricePeriodType = 'range' | 'days' | 'dayOfMonth';

export interface PricePeriodCard {
  id: string;
  name: string;
  type: PricePeriodType;
  rangeFrom?: string;
  rangeTo?: string;
  days?: string[];
  date?: string;
}

export interface EnginePeriod {
  id: string;
  name: string;
  type: PricePeriodType;
  rangeFrom?: string;
  rangeTo?: string;
  days?: string[];
  date?: string;
}

export interface PeriodDiffResult {
  added: EnginePeriod[];
  removed: EnginePeriod[];
  unchanged: EnginePeriod[];
}

export interface PeriodEngineResult {
  periods: EnginePeriod[];
  diff: PeriodDiffResult;
}
