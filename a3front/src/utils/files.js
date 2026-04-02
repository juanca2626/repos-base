import dayjs from 'dayjs';
import 'dayjs/locale/es';
import 'dayjs/locale/en';
import 'dayjs/locale/pt';

import { useFilesStore, useItineraryStore } from '@/stores/files';

const DEFAULT_LOCALE = 'en';

export function formatDate(dateValue, stringFormat = 'DD/MM/YYYY', locale = DEFAULT_LOCALE) {
  if (!dateValue) return '';

  return dayjs(dateValue).locale(locale).format(stringFormat);
}

export function formatDateTime(dateValue, stringFormat = 'DD/MM/YYYY', locale = DEFAULT_LOCALE) {
  if (!dateValue) return '';

  return dayjs(dateValue).locale(locale).format(`${stringFormat} HH:mm:ss`);
}

export function formatDateNight(dateValue, stringFormat = 'DD/MM/YYYY', locale = 'es') {
  if (!dateValue) return '';

  return dayjs(dateValue).add(1, 'day').locale(locale).format(stringFormat);
}

export function formatTime(timeValue, size = 5) {
  return String(timeValue).slice(0, size);
}

export function checkDates(date_in, date_out) {
  let date1 = dayjs(date_in);
  let date2 = dayjs(date_out);
  let days = date2.diff(date1, 'days', true);

  return Number(days) + 1;
}

export function getWeekDay(dateValue = '', locale = DEFAULT_LOCALE) {
  if (!dateValue) return '';
  return dayjs(dateValue).locale(locale).format('dddd');
}

export function truncateString(str, num = 70) {
  if (str.length <= num) return str;
  return str.slice(0, num) + '...';
}

export function formatNumber({
  number,
  currencySymbol = '$',
  digits = 3,
  locales = 'en-US',
  hideCurrencySymbol = true,
}) {
  const OPTIONS = {
    minimumFractionDigits: digits,
    maximumFractionDigits: digits,
  };

  number = number ?? 0;

  const numberFormatInstance = new Intl.NumberFormat(locales, OPTIONS);

  const numberValue = Number(number);

  const formatedNumberValue = numberFormatInstance.format(numberValue);

  if (formatedNumberValue)
    return `${!hideCurrencySymbol ? currencySymbol : ''}${formatedNumberValue}`;

  return `${!hideCurrencySymbol ? currencySymbol : ''}${numberValue}`;
}

export function sortArrayByProp(values, prop) {
  return values.sort((a, b) => a[prop] - b[prop]);
}

export function textPad({ text = '', start = '', end = '', length = 0 }) {
  start = String(start);
  end = String(end);

  if (start != '') {
    return String(text).padStart(length, start);
  }

  if (end != '') {
    return String(text).padEnd(length, end);
  }
}

export function roundLito({ num, module = 'hotel' }) {
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

// FILES..
function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function processInChunks(array, chunkSize, callback) {
  for (let i = 0; i < array.length; i += chunkSize) {
    const chunk = array.slice(i, i + chunkSize);
    await Promise.all(chunk.map(callback));
    await sleep(500);
  }
}

const generateItineraryDetail = async (itinerary_id) => {
  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();

  const itineraryId = itinerary_id;
  await itineraryStore.getById({
    fileId: filesStore.getFile.id,
    itineraryId: itineraryId,
  });
  const newItinerary = itineraryStore.getItinerary;

  if (newItinerary?.id === itineraryId) {
    filesStore.updateItinerary(newItinerary);
  } else {
    filesStore.removeItinerary(itineraryId);
  }
};

export async function processLoadingItineraries() {
  const filesStore = useFilesStore();
  await processInChunks(
    filesStore.getFileItineraries.filter((itinerary) => itinerary.isLoading),
    7, // Puedes ajustar este número según lo que tolere el servidor
    async (itinerary) => generateItineraryDetail(itinerary.id)
  );
}
