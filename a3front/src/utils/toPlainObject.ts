import { isReactive, toRaw } from 'vue';

export const toPlainObject = <T extends object>(obj: T): T => {
  const raw = isReactive(obj) ? toRaw(obj) : obj;

  return JSON.parse(JSON.stringify(raw));
};
