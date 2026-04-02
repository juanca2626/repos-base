export function roundLito(num: number, module: string = ''): string {
  // Convertir el número a un formato con 2 decimales
  const formattedNum = num.toFixed(2); // Devuelve un string
  const [nEntero, nDecimalPart] = formattedNum.split('.'); // Separar la parte entera y decimal

  let newDecimal = 0;
  const nDecimal = parseInt(nDecimalPart || '0', 10); // Convertir la parte decimal a número entero

  // Si el decimal es menor o igual a 10, redondear a 0
  if (nDecimal <= 10) {
    newDecimal = 0;
  } else if (nDecimal > 10 && nDecimal <= 50) {
    // Si el decimal está entre 11 y 50, redondear a 0.5
    newDecimal = 5;

    // Condición específica para el módulo 'hotel'
    if (module === 'hotel') {
      return `${parseInt(nEntero, 10) + 1}.0`; // Incrementar el entero y establecer decimal a 0
    }
  } else {
    // Si el decimal está entre 51 y 99, incrementar el entero y redondear a 0
    return `${parseInt(nEntero, 10) + 1}.0`;
  }

  // Retornar el número redondeado como string
  return `${nEntero}.${newDecimal}`;
}

// utils/paramsToQueryString.ts
export const paramsToQueryString = (params: Record<string, any>): string => {
  const searchParams = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    if (
      value == null ||
      (typeof value === 'string' && value.trim() === '') ||
      (Array.isArray(value) && value.length === 0)
    )
      return;

    Array.isArray(value)
      ? value.forEach((v) => searchParams.append(key, String(v)))
      : searchParams.append(key, String(value));
  });

  return searchParams.toString();
};
