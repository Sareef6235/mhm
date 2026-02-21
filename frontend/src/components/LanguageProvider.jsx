'use client';
import { createContext, useContext, useEffect, useState } from 'react';
import { translations } from '../locales/translations';

const LanguageContext = createContext();

export function LanguageProvider({ children }) {
  const [lang, setLang] = useState('en');
  useEffect(() => {
    const saved = localStorage.getItem('lang');
    if (saved) setLang(saved);
  }, []);

  const changeLanguage = (next) => {
    setLang(next);
    localStorage.setItem('lang', next);
  };

  const t = translations[lang] || translations.en;
  return <LanguageContext.Provider value={{ lang, t, changeLanguage }}>{children}</LanguageContext.Provider>;
}

export const useLang = () => useContext(LanguageContext);
