declare module '*.vue' {
  import { defineComponent } from 'vue';
  const Component: ReturnType<typeof defineComponent>;
  export default Component;
}

declare module 'vue-select' {
  import { DefineComponent } from 'vue';
  const vSelect: DefineComponent<any, any, any>;
  export default vSelect;
}
