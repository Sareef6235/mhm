'use client';
import { useState } from 'react';
import AdminLayout from '../../../components/AdminLayout';
import { api, setToken } from '../../../lib/api';

export default function UploadJsonPage() {
  const [text, setText] = useState('[]');
  const [response, setResponse] = useState('');

  const upload = async () => {
    setToken(localStorage.getItem('token'));
    const data = JSON.parse(text);
    const res = await api.post('/results/bulk', { data });
    setResponse(`Inserted ${res.data.inserted}`);
  };

  return <AdminLayout><h1 className="text-xl mb-2">Upload JSON</h1><textarea className="w-full min-h-64 bg-slate-900 p-3 rounded" value={text} onChange={(e)=>setText(e.target.value)} /><div className="flex gap-2 mt-2"><button onClick={upload} className="bg-indigo-600 px-4 py-2 rounded">Upload</button><a className="bg-emerald-700 px-4 py-2 rounded" href={`data:application/json,${encodeURIComponent(text)}`} download="results.json">Download JSON</a></div><p>{response}</p></AdminLayout>;
}
