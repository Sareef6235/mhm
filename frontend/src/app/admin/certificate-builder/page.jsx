'use client';
import { useEffect, useState } from 'react';
import AdminLayout from '../../../components/AdminLayout';
import { api, setToken } from '../../../lib/api';

export default function CertificateBuilderPage() {
  const [cfg, setCfg] = useState({ title: 'Certificate of Achievement', footer: '', logo_url: '', principal_name: '', signature_url: '', background_url: '', watermark: 'Official', fields_json: [{ key: 'name', x: 100, y: 140 }] });
  useEffect(() => { setToken(localStorage.getItem('token')); api.get('/settings/certificate').then((r)=>Object.keys(r.data).length && setCfg(r.data)).catch(()=>{}); }, []);
  const save = async () => { setToken(localStorage.getItem('token')); await api.post('/settings/certificate', cfg); alert('Saved'); };
  return <AdminLayout><h1 className="text-xl mb-2">Certificate Builder</h1><input className="bg-slate-900 p-2 rounded w-full mb-2" value={cfg.title} onChange={(e)=>setCfg({...cfg,title:e.target.value})} /><textarea className="bg-slate-900 p-2 rounded w-full min-h-48" value={JSON.stringify(cfg.fields_json,null,2)} onChange={(e)=>setCfg({...cfg,fields_json:JSON.parse(e.target.value)})} /><button className="bg-indigo-600 px-4 py-2 rounded mt-2" onClick={save}>Save Template</button></AdminLayout>;
}
