'use client';
import { useEffect, useState } from 'react';
import { api } from '../../../lib/api';

export default function VerifyByLinkPage({ params }) {
  const [text, setText] = useState('Verifying...');
  useEffect(() => {
    api.get(`/results/short/${params.shortCode}`)
      .then(({ data }) => setText(`Verified: ${data.student_name} - ${data.register_number}`))
      .catch(() => setText('Certificate not found'));
  }, [params.shortCode]);

  return <div className="glass rounded-2xl p-6">{text}</div>;
}
