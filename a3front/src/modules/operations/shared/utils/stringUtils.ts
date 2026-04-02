export function getFirstName(str: string) {
  if (str.length === 0) {
    return '';
  }

  const firstLetter = str.charAt(0);
  return ', ' + firstLetter.toUpperCase() + '.';
}

export function getLastName(str: string) {
  // Verificar si la cadena contiene espacios
  const hasSpaces = str.includes(' ');

  if (hasSpaces) {
    // Si la cadena tiene espacios, dividirla en palabras
    const words = str.split(' ');

    // Capitalizar cada palabra
    const capitalizedWords = words.map((word) => {
      return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
    });

    // Unir las palabras capitalizadas en una sola cadena
    const capitalizedString = capitalizedWords.join(' ');

    return capitalizedString;
  } else {
    // Si la cadena no tiene espacios, capitalizar solo la primera letra
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
  }
}

export const toTitleCase = (name: string): string => {
  return name
    .toLowerCase() // Convertimos todo a minúsculas
    .split(' ') // Dividimos por espacios
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalizamos la primera letra
    .join(' '); // Unimos las palabras
};
