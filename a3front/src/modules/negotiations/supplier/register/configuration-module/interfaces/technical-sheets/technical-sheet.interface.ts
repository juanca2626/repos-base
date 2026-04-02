export interface OptionData {
  name: string;
  key: string;
  icon: string;
}

export interface NotificationOption {
  category: string;
  data: OptionData[];
}
