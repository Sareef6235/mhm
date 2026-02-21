'use client';

import useSWR from 'swr';
import api from '../../lib/api';

const fetcher = (url) => api.get(url).then((r) => r.data);

export default function AdminDashboard() {
  const { data } = useSWR('/exams/dashboard/summary', fetcher);
  const cards = data ? [
    ['Total Students', data.totalStudents],
    ['Total Exams', data.totalExams],
    ['Total Searches', data.totalSearches],
    ['Active Links', data.activeLinks]
  ] : [];

  return <div className="grid gap-4 md:grid-cols-2">{cards.map(([label, value]) => <div key={label} className="rounded-xl bg-white/10 p-4"><p>{label}</p><h3 className="text-3xl font-bold">{value}</h3></div>)}</div>;
}
