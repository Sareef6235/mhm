'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import api from '../../../lib/api';

export default function AdminLogin() {
  const router = useRouter();
  const [form, setForm] = useState({ email: '', password: '' });

  const submit = async (e) => {
    e.preventDefault();
    const { data } = await api.post('/auth/login', form);
    localStorage.setItem('token', data.token);
    router.push('/admin');
  };

  return (
    <form onSubmit={submit} className="mx-auto mt-16 w-[95%] max-w-md rounded-2xl p-6 glass space-y-3">
      <h2 className="text-xl font-semibold">Admin Login</h2>
      <input className="w-full rounded-lg bg-white/10 p-2" placeholder="Email" onChange={(e) => setForm({ ...form, email: e.target.value })} />
      <input type="password" className="w-full rounded-lg bg-white/10 p-2" placeholder="Password" onChange={(e) => setForm({ ...form, password: e.target.value })} />
      <button className="w-full rounded-lg bg-indigo-500 p-2">Login</button>
    </form>
  );
}
