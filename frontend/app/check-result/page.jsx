'use client';

import { useState } from 'react';
import toast from 'react-hot-toast';
import api from '../../lib/api';

export default function CheckResultPage() {
  const [form, setForm] = useState({ register_number: '', exam_id: '', dob: '' });
  const [result, setResult] = useState(null);

  const onSearch = async (e) => {
    e.preventDefault();
    try {
      const { data } = await api.get('/results/search', { params: form });
      setResult(data);
    } catch {
      toast.error('Result not found');
    }
  };

  return (
    <div className="mx-auto mt-12 w-[95%] max-w-4xl space-y-6">
      <form onSubmit={onSearch} className="glass rounded-2xl p-5 grid gap-3 md:grid-cols-4">
        <input className="rounded-lg bg-white/10 p-2" placeholder="Exam ID" onChange={(e) => setForm({ ...form, exam_id: e.target.value })} />
        <input className="rounded-lg bg-white/10 p-2" placeholder="Register Number" onChange={(e) => setForm({ ...form, register_number: e.target.value })} />
        <input type="date" className="rounded-lg bg-white/10 p-2" onChange={(e) => setForm({ ...form, dob: e.target.value })} />
        <button className="rounded-lg bg-indigo-500 p-2">Search</button>
      </form>
      {result && (
        <article className="glass rounded-2xl p-6">
          <h2 className="text-2xl font-semibold">{result.student.name}</h2>
          <p>{result.student.register_number} | {result.student.result_status}</p>
          <p>Total: {result.student.total} | Percentage: {result.student.percentage}% | Grade: {result.student.grade}</p>
          <img src={result.qrDataUrl} alt="QR verification" className="mt-4 h-32 w-32 rounded bg-white p-1" />
        </article>
      )}
    </div>
  );
}
