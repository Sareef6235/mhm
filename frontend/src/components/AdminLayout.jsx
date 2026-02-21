'use client';
import Link from 'next/link';
import { useRouter } from 'next/navigation';

const items = [
  ['Dashboard', '/admin/dashboard'],
  ['Import Google Sheet', '/admin/import-sheet'],
  ['Upload JSON', '/admin/upload-json'],
  ['Exam Settings', '/admin/exam-settings'],
  ['Certificate Builder', '/admin/certificate-builder'],
  ['Manage Results', '/admin/manage-results']
];

export default function AdminLayout({ children }) {
  const router = useRouter();
  const logout = () => {
    localStorage.removeItem('token');
    router.push('/login');
  };

  return (
    <div className="grid md:grid-cols-[220px_1fr] gap-4">
      <aside className="glass rounded-2xl p-4 space-y-2">
        {items.map(([label, href]) => <Link className="block hover:text-cyan-300" key={href} href={href}>{label}</Link>)}
        <button onClick={logout} className="mt-4 text-red-300">Logout</button>
      </aside>
      <div className="glass rounded-2xl p-4">{children}</div>
    </div>
  );
}
