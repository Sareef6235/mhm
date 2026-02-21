'use client';
import { useState } from 'react';
import AdminLayout from '../../../components/AdminLayout';
import { api, setToken } from '../../../lib/api';

export default function ImportSheetPage() {
  const [sheetId, setSheetId] = useState('');
  const [sheets, setSheets] = useState([]);
  const [sheetName, setSheetName] = useState('');
  const [headers, setHeaders] = useState([]);
  const [jsonData, setJsonData] = useState([]);

  const loadSheets = async () => {
    setToken(localStorage.getItem('token'));
    const { data } = await api.post('/import/sheets', { sheetId });
    setSheets(data.sheets);
  };

  const loadHeaders = async () => {
    const { data } = await api.post('/import/headers', { sheetId, sheetName });
    setHeaders(data.headers);
  };

  const transform = async () => {
    const mapping = { registerNumber: headers[0], name: headers[1], school: headers[2], photo: headers[3], dob: headers[4] };
    const subjectColumns = headers.slice(5);
    const { data } = await api.post('/import/transform', { sheetId, sheetName, mapping, subjectColumns });
    setJsonData(data.data);
  };

  const save = async () => {
    await api.post('/results/bulk', { data: jsonData });
    alert('Imported into MySQL');
  };

  return <AdminLayout><h1 className="text-xl mb-2">Google Sheets Import</h1><input className="bg-slate-900 p-2 rounded w-full" value={sheetId} onChange={(e)=>setSheetId(e.target.value)} placeholder="Google Sheet ID" /><button className="bg-indigo-600 px-3 py-1 rounded mt-2" onClick={loadSheets}>Fetch Sheets</button><div className="mt-2 flex gap-2">{sheets.map(s=><button key={s} className="bg-white/10 px-2 py-1 rounded" onClick={()=>setSheetName(s)}>{s}</button>)}</div>{sheetName && <div className="mt-3 space-x-2"><button className="bg-cyan-700 px-3 py-1 rounded" onClick={loadHeaders}>Preview Headers</button><button className="bg-emerald-700 px-3 py-1 rounded" onClick={transform}>Transform</button><button className="bg-indigo-700 px-3 py-1 rounded" onClick={save}>Save to MySQL</button></div>}<pre className="text-xs mt-3 overflow-auto">{JSON.stringify({headers, jsonData: jsonData.slice(0,3)}, null, 2)}</pre></AdminLayout>;
}
