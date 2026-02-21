'use client';

import Link from 'next/link';
import { useTranslation } from 'react-i18next';
import '../../lib/i18n';
import LanguageSwitcher from './LanguageSwitcher';

export default function Navbar() {
  const { t } = useTranslation();
  return (
    <nav className="glass sticky top-0 z-40 mx-auto mt-4 flex w-[95%] max-w-6xl items-center justify-between rounded-2xl px-5 py-3">
      <h1 className="font-bold">ResultPro</h1>
      <div className="flex items-center gap-4 text-sm">
        <Link href="/">{t('home')}</Link>
        <Link href="/check-result">{t('checkResult')}</Link>
        <Link href="/verify-certificate">{t('verifyCertificate')}</Link>
        <Link href="/contact">{t('contact')}</Link>
        <LanguageSwitcher />
      </div>
    </nav>
  );
}
