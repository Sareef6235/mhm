'use client';

import { motion } from 'framer-motion';
import { useTranslation } from 'react-i18next';
import '../lib/i18n';

export default function HomePage() {
  const { t } = useTranslation();

  return (
    <section className="mx-auto mt-16 w-[95%] max-w-5xl rounded-3xl p-10 glass text-center">
      <motion.h1 initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="text-4xl font-bold">{t('heroTitle')}</motion.h1>
      <motion.p initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.2 }} className="mt-4 text-slate-300">{t('heroSubtitle')}</motion.p>
    </section>
  );
}
