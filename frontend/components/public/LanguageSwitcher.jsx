'use client';

import { useTranslation } from 'react-i18next';
import '../../lib/i18n';

const languages = ['en', 'ml', 'hi', 'ta', 'ar'];

export default function LanguageSwitcher() {
  const { i18n } = useTranslation();

  return (
    <select
      className="glass rounded-lg px-2 py-1 text-sm"
      value={i18n.language}
      onChange={(e) => i18n.changeLanguage(e.target.value)}
    >
      {languages.map((lang) => <option key={lang} value={lang}>{lang.toUpperCase()}</option>)}
    </select>
  );
}
