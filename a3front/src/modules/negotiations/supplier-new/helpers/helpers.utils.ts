export function pluck<T, K extends keyof T & string>(array: T[], key: string): T[K][] {
  return array.map((item: any) => item[key]);
}
