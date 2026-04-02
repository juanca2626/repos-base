import type { Ref } from 'vue';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';

export interface FilterableCheckboxListProps {
  options: SelectOption[];
  chunk?: number;
  placeholder?: string;
}

export interface FilterableCheckboxListEmits {
  (e: 'loading', value: boolean): void;
}

export interface FilterableCheckboxListParams {
  options: Ref<SelectOption[]>;
  selectedOptionsModel: Ref<any[]>;
  searchModel: Ref<string>;
  chunkSize?: number;
  emit: FilterableCheckboxListEmits;
}
