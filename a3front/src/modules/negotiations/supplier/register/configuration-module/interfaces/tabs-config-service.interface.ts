import type { Component } from 'vue';
import type { TabKeyEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/tab-key.enum';

export interface TabConfig {
  key: TabKeyEnum;
  tab: string;
  component: Component;
}

export interface TabOrderMapping {
  key: TabKeyEnum;
  tab: string;
  component: Component;
  order: number;
}
