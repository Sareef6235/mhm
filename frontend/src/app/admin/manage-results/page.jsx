'use client';
import { useEffect, useState } from 'react';
import AdminLayout from '../../../components/AdminLayout';
import { api, setToken } from '../../../lib/api';

export default function ManageResultsPage() {
  const [rows, setRows] = useState([]);
  const load = () => { setToken(localStorage.getItem('token')); api.get('/results').then((r)=>setRows(r.data)); };
  useEffect(load, []);
  const toggle = async (id) => { await api.patch(`/results/${id}/toggle`); load(); };
  const remove = async (id) => { await api.delete(`/results/${id}`); load(); };
  return <AdminLayout><h1 className="text-xl mb-3">Manage Results</h1><div className="space-y-2">{rows.map(r=><div key={r.id} className="p-3 rounded bg-white/5 flex justify-between"><span>{r.register_number} - {r.student_name} - /r/{r.short_code}</span><span className="space-x-2"><button onClick={()=>toggle(r.id)} className="bg-amber-700 px-2 py-1 rounded">{r.is_enabled?'Disable':'Enable'}</button><button onClick={()=>remove(r.id)} className="bg-red-700 px-2 py-1 rounded">Delete</button></span></div>)}</div></AdminLayout>;
}
