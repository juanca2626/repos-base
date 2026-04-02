declare module 'vue-select' {
  import { DefineComponent } from 'vue';

  interface VueSelectProps {
    options?: any[];
    value?: any;
    modelValue?: any;
    placeholder?: string;
    disabled?: boolean;
    autocomplete?: string;
    reduce?: (option: any) => any;
    label?: string;
    [key: string]: any;
  }

  const vSelect: DefineComponent<VueSelectProps>;
  export default vSelect;
}
