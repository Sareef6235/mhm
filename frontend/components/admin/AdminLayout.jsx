'use client';

import Link from 'next/link';

const links = [
  ['Dashboard', '/admin'],
  ['Import Google Sheet', '/admin/import-google-sheet'],
  ['Upload JSON', '/admin/upload-json'],
  ['Exam Settings', '/admin/exam-settings'],
  ['Certificate Builder', '/admin/certificate-builder'],
  ['Manage Results', '/admin/manage-results']
];

export default function AdminLayout({ children }) {
  return (
    <div className="mx-auto grid min-h-screen max-w-7xl grid-cols-1 gap-4 p-4 md:grid-cols-[260px_1fr]">
      <aside className="glass rounded-2xl p-4">
        <h2 className="mb-3 font-semibold">Admin Panel</h2>
        <div className="space-y-2 text-sm">
          {links.map(([label, href]) => <Link className="block rounded-lg px-3 py-2 hover:bg-white/10" href={href} key={href}>{label}</Link>)}
        </div>
      </aside>
      <main className="glass rounded-2xl p-5">{children}</main>
    </div>
  );
}
