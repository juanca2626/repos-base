import fs from 'fs/promises';
import path from 'path';
import axios from 'axios';
import { fileURLToPath } from 'url';

// __dirname para ESM
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Aplanar JSON
function flattenJSON(obj, prefix = '', result = {}) {
  for (const key in obj) {
    const value = obj[key];
    const prefixedKey = prefix ? `${prefix}.${key}` : key;
    if (typeof value === 'object' && value !== null) {
      flattenJSON(value, prefixedKey, result);
    } else {
      result[prefixedKey] = value;
    }
  }
  return result;
}

// Reconstruir JSON desde plano
function rebuildJSON(flat) {
  const result = {};
  for (const [key, value] of Object.entries(flat)) {
    const keys = key.split('.');
    let temp = result;
    keys.forEach((k, i) => {
      if (i === keys.length - 1) {
        temp[k] = value;
      } else {
        temp[k] = temp[k] || {};
        temp = temp[k];
      }
    });
  }
  return result;
}

// Traduce un texto a varios idiomas
async function translateToMultipleLangs(text, source, targets) {
  const params = {
    text: text.split(' ').length > 1 ? text : text.toLowerCase(),
    sourceLang: 'es-MX',
    targetLangs: targets,
  };

  const response = await axios.post(
    'https://ki9h7ncn8d.execute-api.us-east-1.amazonaws.com/LambdaTranslate',
    params
  );

  const result = {};
  for (const translation of response.data.translations) {
    result[translation.language] = translation.translatedText;
  }

  return result;
}

async function translateFlatLineByLine(flatText, source, targets, existingTranslationsPerLang) {
  const resultsPerLang = Object.fromEntries(
    targets.map((lang) => [lang, { ...(existingTranslationsPerLang[lang] || {}) }])
  );

  for (const [key, value] of Object.entries(flatText)) {
    if (!value) {
      targets.forEach((lang) => {
        resultsPerLang[lang][key] = '';
      });
      continue;
    }

    // Saltar si ya está traducido
    let allTranslated = targets.every((lang) => {
      const existing = resultsPerLang[lang][key];
      return existing && existing.trim() !== '';
    });
    if (allTranslated) continue;

    try {
      const translations = await translateToMultipleLangs(value, source, targets);
      targets.forEach((lang) => {
        resultsPerLang[lang][key] = translations[lang] || '';
      });
      console.log(`✔ ${key}: ${value}`);
    } catch (err) {
      console.error(`❌ Error al traducir "${key}": ${err.message}`);
      targets.forEach((lang) => {
        resultsPerLang[lang][key] = '';
      });
    }
  }

  return resultsPerLang;
}

// Configuración
const SOURCE_LANG = 'es';
const TARGET_LANGS = ['en', 'pt'];
const basePath = path.join(__dirname, '../src/lang');
const FILES = ['files.json', 'global.json'];

// Proceso principal
for (const file of FILES) {
  const sourcePath = path.join(basePath, `${SOURCE_LANG}/${file}`);
  const raw = await fs.readFile(sourcePath, 'utf-8');
  const json = JSON.parse(raw);
  const flatText = flattenJSON(json);

  console.log(`\n🌐 Traduciendo ${file} a: ${TARGET_LANGS.join(', ')}`);

  const existingTranslationsPerLang = {};

  for (const lang of TARGET_LANGS) {
    const outputPath = path.join(basePath, `${lang}/${file}`);
    let existing = {};
    try {
      const existingRaw = await fs.readFile(outputPath, 'utf-8');
      existing = flattenJSON(JSON.parse(existingRaw));
    } catch {
      // No existe aún el archivo
    }
    existingTranslationsPerLang[lang] = existing;
  }

  const translations = await translateFlatLineByLine(
    flatText,
    SOURCE_LANG,
    TARGET_LANGS,
    existingTranslationsPerLang
  );

  for (const lang of TARGET_LANGS) {
    const rebuilt = rebuildJSON(translations[lang]);
    const outputPath = path.join(basePath, `${lang}/${file}`);
    await fs.mkdir(path.dirname(outputPath), { recursive: true });
    await fs.writeFile(outputPath, JSON.stringify(rebuilt, null, 2), 'utf-8');
    console.log(`✅ Archivo ${lang}/${file} generado.`);
  }
}
