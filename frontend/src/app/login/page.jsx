'use client';
import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { api, setToken } from '../../lib/api';

export default function LoginPage() {
  const [username, setUsername] = useState('admin');
  const [password, setPassword] = useState('admin123');
  const [error, setError] = useState('');
  const router = useRouter();

  const submit = async () => {
    try {
      const { data } = await api.post('/auth/login', { username, password });
      localStorage.setItem('token', data.token);
      setToken(data.token);
      router.push('/admin/dashboard');
    } catch (e) {
      setError(e.response?.data?.message || 'Login failed');
    }
  };

  return (
    <div className="max-w-md mx-auto glass rounded-2xl p-6 space-y-3">
      <h2 className="text-2xl font-semibold">Admin Login</h2>
      <input className="bg-slate-800 px-3 py-2 rounded w-full" value={username} onChange={(e)=>setUsername(e.target.value)} />
      <input type="password" className="bg-slate-800 px-3 py-2 rounded w-full" value={password} onChange={(e)=>setPassword(e.target.value)} />
      {error && <p className="text-red-300">{error}</p>}
      <button onClick={submit} className="bg-indigo-600 px-4 py-2 rounded">Login</button>
    </div>
  );
}
