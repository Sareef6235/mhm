import { redirect } from 'next/navigation';

export default async function ShortRoute({ params }) {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/results/short/${params.shortcode}`, { cache: 'no-store' });
  if (!res.ok) redirect('/404');
  const data = await res.json();
  redirect(`/result/${data.exam_id}`);
}
