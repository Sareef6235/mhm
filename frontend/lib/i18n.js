'use client';

import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import en from '../locales/en/common.json';
import ml from '../locales/ml/common.json';
import hi from '../locales/hi/common.json';
import ta from '../locales/ta/common.json';
import ar from '../locales/ar/common.json';

if (!i18n.isInitialized) {
  i18n.use(initReactI18next).init({
    resources: { en: { translation: en }, ml: { translation: ml }, hi: { translation: hi }, ta: { translation: ta }, ar: { translation: ar } },
    lng: 'en',
    fallbackLng: 'en',
    interpolation: { escapeValue: false }
  });
}

export default i18n;
