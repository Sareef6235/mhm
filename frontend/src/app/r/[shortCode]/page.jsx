'use client';
import { useEffect } from 'react';
import { useRouter } from 'next/navigation';

export default function ShortPage({ params }) {
  const router = useRouter();
  useEffect(() => {
    router.replace(`/verify/${params.shortCode}`);
  }, [params.shortCode, router]);
  return <div className="glass rounded-2xl p-6">Redirecting...</div>;
}
