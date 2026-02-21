'use client';

import useSWR from 'swr';
import api from '../../../lib/api';

const fetcher = (url) => api.get(url).then((r) => r.data);

export default function ManageResultsPage() {
  const { data, mutate } = useSWR('/results/links', fetcher);

  const toggle = async (id) => {
    await api.patch(`/results/links/${id}/toggle`);
    mutate();
  };

  return <div className="space-y-2">{data?.map((link) => <div key={link.id} className="rounded-lg bg-white/10 p-3 flex justify-between"><span>{link.Exam?.title} /r/{link.short_code}</span><button onClick={() => toggle(link.id)}>{link.is_enabled ? 'Disable' : 'Enable'}</button></div>)}</div>;
}
