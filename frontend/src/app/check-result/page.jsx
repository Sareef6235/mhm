'use client';
import { useState } from 'react';
import { api } from '../../lib/api';
import ResultCard from '../../components/ResultCard';

export default function CheckResultPage() {
  const [registerNumber, setRegisterNumber] = useState('');
  const [dob, setDob] = useState('');
  const [result, setResult] = useState(null);
  const [error, setError] = useState('');

  const search = async () => {
    try {
      setError('');
      const { data } = await api.get('/results/search', { params: { registerNumber, dob } });
      setResult(data);
    } catch (e) {
      setError(e.response?.data?.message || 'Failed to search result');
    }
  };

  return (
    <section className="space-y-4">
      <div className="glass rounded-2xl p-4 flex flex-col md:flex-row gap-3">
        <input placeholder="Register Number" className="bg-slate-800 px-3 py-2 rounded" value={registerNumber} onChange={(e) => setRegisterNumber(e.target.value)} />
        <input type="date" className="bg-slate-800 px-3 py-2 rounded" value={dob} onChange={(e) => setDob(e.target.value)} />
        <button className="bg-indigo-600 px-4 py-2 rounded" onClick={search}>Search</button>
      </div>
      {error && <p className="text-red-300">{error}</p>}
      {result && <ResultCard result={result} />}
    </section>
  );
}
