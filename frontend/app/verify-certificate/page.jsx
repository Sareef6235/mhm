'use client';

import { useSearchParams } from 'next/navigation';
import useSWR from 'swr';
import api from '../../lib/api';

const fetcher = (url) => api.get(url).then((res) => res.data);

export default function VerifyCertificatePage() {
  const params = useSearchParams();
  const student = params.get('student');
  const { data } = useSWR(student ? `/results/verify-certificate?student=${student}` : null, fetcher);

  return <div className="mx-auto mt-10 w-[95%] max-w-3xl glass rounded-2xl p-6">{data?.valid ? `Certificate valid for ${data.student.name}` : 'Provide a verification URL.'}</div>;
}
