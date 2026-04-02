export const padWithZero = (n: number): string => {
  return n >= 1 && n <= 9 ? `0${n}` : `${n}`;
};
