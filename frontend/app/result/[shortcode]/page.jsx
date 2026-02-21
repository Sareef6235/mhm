import Link from 'next/link';

export default function ResultLanding({ params }) {
  return <div className="mx-auto mt-10 w-[95%] max-w-3xl glass rounded-2xl p-6">Use exam <b>{params.shortcode}</b> on <Link href="/check-result">Check Result</Link>.</div>;
}
