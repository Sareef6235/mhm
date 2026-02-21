'use client';

import { useState } from 'react';
import api from '../../../lib/api';

export default function UploadJsonPage() {
  const [json, setJson] = useState('[]');

  const upload = async () => {
    await api.post('/results/students/upload', { exam_id: 1, rows: JSON.parse(json) });
    alert('Uploaded');
  };

  return <div className="space-y-3"><textarea className="h-64 w-full rounded-lg bg-white/10 p-3" value={json} onChange={(e) => setJson(e.target.value)} /><button onClick={upload} className="rounded-lg bg-indigo-500 px-3 py-2">Upload JSON</button></div>;
}
