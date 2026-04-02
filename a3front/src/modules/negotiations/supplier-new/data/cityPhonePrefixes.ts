/**
 * cityPhonePrefixes.ts
 *
 * Mapa de prefijos telefónicos (código de área) por ciudad.
 * Clave: nombre de ciudad normalizado (minúsculas, sin acentos).
 * Valor: código de área como string (mantiene ceros iniciales).
 *
 * Cobertura: Perú, Chile, Colombia, Ecuador, Argentina.
 * Para otros países el campo de prefijo se dejará vacío.
 */

/** Normaliza un texto: minúsculas y sin acentos */
export function normalizeCityName(name: string): string {
  return name
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim();
}

/** Extrae el nombre de ciudad desde un label completo del selector de ubicación.
 *  El label puede venir como "Perú, Lima, Lima" o "Lima" o "Argentina / Buenos Aires".
 *  Se toma el último segmento significativo.
 */
export function extractCityNameFromLabel(label: string | undefined | null): string {
  if (!label) return '';
  // Separadores más comunes: " / ", ", ", " - "
  const parts = label.split(/\s*[\/,]\s*/);
  // El nombre de ciudad suele ser el último fragmento no vacío
  for (let i = parts.length - 1; i >= 0; i--) {
    const part = parts[i].trim();
    if (part) return part;
  }
  return label.trim();
}

// ---------------------------------------------------------------------------
// PERÚ — Prefijos por ciudad/departamento (Código de área sin el 0 inicial)
// Referencia: Osiptel — https://www.osiptel.gob.pe/
// ---------------------------------------------------------------------------
const PERU_PREFIXES: Record<string, string> = {
  // Lima y Callao
  lima: '01',
  callao: '01',

  // La Libertad
  trujillo: '044',
  chepen: '044',
  pacasmayo: '044',
  ascope: '044',
  viru: '044',
  otuzco: '044',
  sanchez_carrion: '044',
  santiago_de_chuco: '044',

  // Arequipa
  arequipa: '054',
  camana: '054',
  caylloma: '054',
  islay: '054',

  // Cusco
  cusco: '084',
  cuzco: '084',
  calca: '084',
  canchis: '084',
  espinar: '084',
  la_convencion: '084',
  urubamba: '084',

  // Piura
  piura: '073',
  sullana: '073',
  talara: '073',
  tumbes: '072',

  // Lambayeque
  chiclayo: '074',
  lambayeque: '074',
  ferrenafe: '074',

  // Junin
  huancayo: '064',
  tarma: '064',
  la_oroya: '064',
  chanchamayo: '064',

  // Ica
  ica: '056',
  nazca: '056',
  pisco: '056',
  chincha: '056',
  palpa: '056',

  // Ancash
  huaraz: '043',
  chimbote: '043',
  casma: '043',

  // Loreto
  iquitos: '065',
  requena: '065',

  // San Martín
  tarapoto: '042',
  moyobamba: '042',
  rioja: '042',

  // Huanuco
  huanuco: '062',
  tingo_maria: '062',

  // Puno
  puno: '051',
  juliaca: '051',

  // Cajamarca
  cajamarca: '076',

  // Ayacucho
  ayacucho: '066',

  // Moquegua
  moquegua: '053',
  ilo: '053',

  // Tacna
  tacna: '052',

  // Ucayali
  pucallpa: '061',

  // Amazonas
  chachapoyas: '041',

  // Apurimac
  abancay: '083',
  andahuaylalas: '083',

  // Huancavelica
  huancavelica: '067',

  // Madre de Dios
  puerto_maldonado: '082',

  // Pasco
  cerro_de_pasco: '063',
  oxapampa: '063',
};

// ---------------------------------------------------------------------------
// CHILE — Prefijos por ciudad (sin el 0 inicial)
// Referencia: Subtel / ITU — Prefijos de 2 dígitos de área
// ---------------------------------------------------------------------------
const CHILE_PREFIXES: Record<string, string> = {
  // Región Metropolitana
  santiago: '2',
  providencia: '2',
  las_condes: '2',
  maipu: '2',
  puente_alto: '2',
  san_bernardo: '2',

  // Valparaíso — Región
  valparaiso: '32',
  vina_del_mar: '32',
  quilpue: '32',
  villa_alemana: '32',
  quillota: '33',
  san_antonio: '35',
  los_andes: '34',
  rancagua: '72',

  // Biobío
  concepcion: '41',
  talcahuano: '41',
  chillan: '42',
  los_angeles: '43',
  coronel: '41',

  // Araucanía
  temuco: '45',
  padre_las_casas: '45',
  nueva_imperial: '45',

  // Maule
  talca: '71',
  curico: '75',
  linares: '73',
  constitucion: '71',

  // Los Lagos
  puerto_montt: '65',
  osorno: '64',
  castro: '65',

  // Los Ríos
  valdivia: '63',

  // Aysén
  coyhaique: '67',

  // Magallanes
  punta_arenas: '61',

  // Antofagasta
  antofagasta: '55',
  calama: '55',
  tocopilla: '55',

  // Atacama
  copiapo: '52',
  vallenar: '51',

  // Coquimbo
  la_serena: '51',
  coquimbo: '51',
  ovalle: '53',
  illapel: '53',

  // Arica y Parinacota
  arica: '58',
  parinacota: '58',

  // Tarapacá
  iquique: '57',
  alto_hospicio: '57',
};

// ---------------------------------------------------------------------------
// COLOMBIA — Prefijos por ciudad
// Referencia: MinTIC — indicativos de área
// ---------------------------------------------------------------------------
const COLOMBIA_PREFIXES: Record<string, string> = {
  // Bogotá D.C.
  bogota: '1',
  bogotá: '1',

  // Antioquia
  medellin: '4',
  medellín: '4',
  bello: '4',
  itagui: '4',
  envigado: '4',
  apartado: '4',
  turbo: '4',

  // Valle del Cauca
  cali: '2',
  buenaventura: '2',
  palmira: '2',
  buga: '2',
  tulua: '2',

  // Atlántico
  barranquilla: '5',

  // Bolivar
  cartagena: '5',
  magangue: '5',

  // Santander
  bucaramanga: '7',
  floridablanca: '7',
  giron: '7',
  piedecuesta: '7',
  barrancabermeja: '7',

  // Cundinamarca
  soacha: '1',
  facatativa: '1',
  zipaquira: '1',

  // Norte de Santander
  cucuta: '7',

  // Nariño
  pasto: '2',

  // Risaralda
  pereira: '6',
  dosquebradas: '6',

  // Quindio
  armenia: '6',

  // Caldas
  manizales: '6',

  // Tolima
  ibague: '8',

  // Huila
  neiva: '8',

  // Boyacá
  tunja: '8',

  // Cauca
  popayan: '2',

  // Meta
  villavicencio: '8',

  // Córdoba
  monteria: '4',
  planeta_rica: '4',

  // Sucre
  sincelejo: '5',

  // Cesar
  valledupar: '5',

  // Magdalena
  santa_marta: '5',

  // Guajira
  riohacha: '5',

  // Chocó
  quibdo: '4',

  // Putumayo
  mocoa: '8',

  // Caquetá
  florencia: '8',

  // Amazonas
  leticia: '8',

  // Arauca
  arauca: '7',

  // Casanare
  yopal: '8',

  // Vichada
  puerto_carreno: '8',

  // Guainia
  inirida: '8',

  // Vaupés
  mitu: '8',
};

// ---------------------------------------------------------------------------
// ECUADOR — Prefijos por ciudad
// Referencia: ARCOTEL — indicativos de área
// ---------------------------------------------------------------------------
const ECUADOR_PREFIXES: Record<string, string> = {
  // Pichincha
  quito: '2',
  cayambe: '2',
  mejia: '2',

  // Guayas
  guayaquil: '4',
  daule: '4',
  milagro: '4',
  duran: '4',
  samborondon: '4',

  // Azuay
  cuenca: '7',

  // Manabí
  portoviejo: '5',
  manta: '5',
  chone: '5',
  jipijapa: '5',

  // El Oro
  machala: '7',
  santa_rosa: '7',
  pasaje: '7',

  // Tungurahua
  ambato: '3',

  // Los Ríos
  quevedo: '5',
  babahoyo: '5',
  vinces: '5',

  // Santo Domingo
  santo_domingo: '2',

  // Imbabura
  ibarra: '6',
  otavalo: '6',

  // Loja
  loja: '7',

  // Cotopaxi
  latacunga: '3',

  // Chimborazo
  riobamba: '3',

  // Bolívar
  guaranda: '3',

  // Carchi
  tulcan: '6',

  // Esmeraldas
  esmeraldas: '6',

  // Sucumbíos
  nueva_loja: '6',
  lago_agrio: '6',

  // Orellana
  francisco_de_orellana: '6',

  // Napo
  tena: '6',

  // Pastaza
  puyo: '3',

  // Morona Santiago
  macas: '7',

  // Zamora Chinchipe
  zamora: '7',

  // Galápagos
  puerto_baquerizo_moreno: '5',
};

// ---------------------------------------------------------------------------
// ARGENTINA — Prefijos por ciudad (código de área sin el 0 inicial)
// Referencia: ENACOM / CNC — Argentina usa 011 para AMBA (se muestra como 11)
// ---------------------------------------------------------------------------
const ARGENTINA_PREFIXES: Record<string, string> = {
  // Buenos Aires (AMBA)
  'buenos aires': '11',
  'ciudad autonoma de buenos aires': '11',
  caba: '11',
  la_plata: '221',
  mar_del_plata: '223',
  quilmes: '11',
  lanus: '11',
  lomas_de_zamora: '11',
  morón: '11',
  moron: '11',
  san_justo: '11',
  'tres de febrero': '11',
  'general san martin': '11',

  // Córdoba
  cordoba: '351',
  villa_carlos_paz: '3541',
  rio_cuarto: '358',
  alta_gracia: '3547',

  // Rosario / Santa Fe
  rosario: '341',
  'santa fe': '342',
  rafaela: '3492',
  venado_tuerto: '3462',

  // Mendoza
  mendoza: '261',
  godoy_cruz: '261',
  san_rafael: '2627',

  // Tucumán
  san_miguel_de_tucuman: '381',
  tucuman: '381',

  // Salta
  salta: '387',

  // Misiones
  posadas: '376',

  // Entre Ríos
  parana: '343',
  concordia: '345',

  // Chaco
  resistencia: '362',

  // Corrientes
  corrientes: '379',

  // Santiago del Estero
  santiago_del_estero: '385',

  // Jujuy
  san_salvador_de_jujuy: '388',
  jujuy: '388',

  // Formosa
  formosa: '370',

  // La Rioja
  la_rioja: '380',

  // San Juan
  san_juan: '264',

  // San Luis
  san_luis: '266',

  // Catamarca
  san_fernando_del_valle_de_catamarca: '383',
  catamarca: '383',

  // Río Negro
  bariloche: '294',
  san_carlos_de_bariloche: '294',
  viedma: '2920',

  // Neuquén
  neuquen: '299',
  //'zapala': '2942',

  // La Pampa
  santa_rosa: '2954',

  // Chubut
  rawson: '2965',
  comodoro_rivadavia: '297',
  trelew: '2965',

  // Santa Cruz
  rio_gallegos: '2966',

  // Tierra del Fuego
  ushuaia: '2901',
};

// ---------------------------------------------------------------------------
// Mapa combinado: nombre normalizado → prefijo
// ---------------------------------------------------------------------------
export const CITY_PHONE_PREFIXES_BY_NAME: Record<string, string> = {
  ...Object.fromEntries(
    Object.entries(PERU_PREFIXES).map(([k, v]) => [normalizeCityName(k.replace(/_/g, ' ')), v])
  ),
  ...Object.fromEntries(
    Object.entries(CHILE_PREFIXES).map(([k, v]) => [normalizeCityName(k.replace(/_/g, ' ')), v])
  ),
  ...Object.fromEntries(
    Object.entries(COLOMBIA_PREFIXES).map(([k, v]) => [normalizeCityName(k.replace(/_/g, ' ')), v])
  ),
  ...Object.fromEntries(
    Object.entries(ECUADOR_PREFIXES).map(([k, v]) => [normalizeCityName(k.replace(/_/g, ' ')), v])
  ),
  ...Object.fromEntries(
    Object.entries(ARGENTINA_PREFIXES).map(([k, v]) => [normalizeCityName(k.replace(/_/g, ' ')), v])
  ),
};

/**
 * Obtiene el prefijo de área para una ciudad dado su nombre (en cualquier formato).
 * Estrategia de búsqueda (en orden de prioridad):
 *  1. Match exacto con el nombre normalizado.
 *  2. Match exacto con el último segmento del label (ciudad extraída).
 *  3. Búsqueda por keyword: si alguna clave del mapa está contenida en el texto,
 *     se ordena por longitud descendente para preferir la coincidencia más específica.
 * Retorna undefined si no hay coincidencia (país no cubierto o ciudad desconocida).
 */
export function getCityPhonePrefix(cityName: string | null | undefined): string | undefined {
  if (!cityName) return undefined;

  const normalized = normalizeCityName(cityName);

  // 1. Match exacto
  if (CITY_PHONE_PREFIXES_BY_NAME[normalized] !== undefined) {
    return CITY_PHONE_PREFIXES_BY_NAME[normalized];
  }

  // 2. Match exacto del último segmento (ej: "Region metropolitana de santiago" → "santiago")
  const extracted = normalizeCityName(extractCityNameFromLabel(cityName));
  if (extracted && CITY_PHONE_PREFIXES_BY_NAME[extracted] !== undefined) {
    return CITY_PHONE_PREFIXES_BY_NAME[extracted];
  }

  // 3. Keyword matching con word-boundary: la clave debe aparecer como palabra completa
  // Ordenar por longitud descendente para preferir coincidencias más específicas
  const keys = Object.keys(CITY_PHONE_PREFIXES_BY_NAME).sort((a, b) => b.length - a.length);
  for (const key of keys) {
    // Escapar caracteres especiales de regex en la key
    const escapedKey = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const wordBoundaryRegex = new RegExp(`(^|\\s)${escapedKey}(\\s|$)`);
    if (wordBoundaryRegex.test(normalized) || wordBoundaryRegex.test(extracted)) {
      return CITY_PHONE_PREFIXES_BY_NAME[key];
    }
  }

  return undefined;
}
