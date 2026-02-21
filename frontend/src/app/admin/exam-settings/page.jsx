'use client';
import { useEffect, useState } from 'react';
import AdminLayout from '../../../components/AdminLayout';
import { api, setToken } from '../../../lib/api';

export default function ExamSettingsPage() {
  const [settings, setSettings] = useState({ total_marks: 600, pass_mark: 35, grade_system: [{ grade: 'A+', min: 90, max: 100 }] });
  const save = async () => {
    setToken(localStorage.getItem('token'));
    await api.post('/settings/exam', settings);
    alert('Saved');
  };
  useEffect(() => { setToken(localStorage.getItem('token')); api.get('/settings/exam').then((r)=>setSettings(r.data)).catch(()=>{}); }, []);
  return <AdminLayout><h1 className="text-xl mb-2">Exam Settings</h1><input className="bg-slate-900 p-2 rounded mr-2" type="number" value={settings.total_marks} onChange={(e)=>setSettings({...settings,total_marks:Number(e.target.value)})} /><input className="bg-slate-900 p-2 rounded" type="number" value={settings.pass_mark} onChange={(e)=>setSettings({...settings,pass_mark:Number(e.target.value)})} /><textarea className="w-full min-h-40 bg-slate-900 p-3 rounded mt-2" value={JSON.stringify(settings.grade_system, null, 2)} onChange={(e)=>setSettings({...settings,grade_system:JSON.parse(e.target.value)})}/><button onClick={save} className="bg-indigo-600 px-4 py-2 rounded mt-2">Save</button></AdminLayout>;
}
