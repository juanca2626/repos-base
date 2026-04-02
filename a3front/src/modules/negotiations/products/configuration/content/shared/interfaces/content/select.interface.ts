export interface SelectOption {
  label: string;
  value: string | number;
}

export interface SelectOptionTextType extends SelectOption {
  contentLength: {
    type: string;
    max: number;
  };
}
