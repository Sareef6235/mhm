'use client';
import { useEffect, useState } from 'react';
import AdminLayout from '../../../components/AdminLayout';
import { api, setToken } from '../../../lib/api';

export default function DashboardPage() {
  const [analytics, setAnalytics] = useState([]);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      setToken(token);
      api.get('/results/analytics').then((r) => setAnalytics(r.data));
    }
  }, []);

  return <AdminLayout><h1 className="text-2xl font-semibold mb-4">Dashboard</h1><pre className="text-xs overflow-auto">{JSON.stringify(analytics, null, 2)}</pre></AdminLayout>;
}
