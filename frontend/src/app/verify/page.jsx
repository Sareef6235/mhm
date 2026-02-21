'use client';
import { useState } from 'react';
import { api } from '../../lib/api';

export default function VerifyPage() {
  const [code, setCode] = useState('');
  const [message, setMessage] = useState('');

  const verify = async () => {
    try {
      const { data } = await api.get(`/results/short/${code}`);
      setMessage(`Valid certificate for ${data.student_name} (${data.register_number})`);
    } catch {
      setMessage('Invalid or disabled certificate link');
    }
  };

  return <div className="glass rounded-2xl p-6 space-y-3"><input className="bg-slate-800 px-3 py-2 rounded w-full" placeholder="Enter short code" value={code} onChange={(e)=>setCode(e.target.value)} /><button onClick={verify} className="bg-indigo-600 px-4 py-2 rounded">Verify</button><p>{message}</p></div>;
}
