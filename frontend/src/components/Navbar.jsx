'use client';
import Link from 'next/link';
import { useLang } from './LanguageProvider';

export default function Navbar() {
  const { t, lang, changeLanguage } = useLang();
  return (
    <header className="glass rounded-2xl p-4 flex flex-wrap gap-3 justify-between items-center">
      <div className="font-semibold text-lg">Exam Results</div>
      <nav className="flex gap-4 text-sm">
        <Link href="/">{t.home}</Link>
        <Link href="/check-result">{t.checkResult}</Link>
        <Link href="/verify">{t.verify}</Link>
        <Link href="/contact">{t.contact}</Link>
        <Link href="/login">{t.adminLogin}</Link>
      </nav>
      <select className="bg-slate-800 rounded px-2 py-1" value={lang} onChange={(e) => changeLanguage(e.target.value)}>
        <option value="en">EN</option><option value="ml">ML</option><option value="hi">HI</option><option value="ta">TA</option><option value="ar">AR</option>
      </select>
    </header>
  );
}
