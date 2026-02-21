'use client';
import { useRef } from 'react';
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';

export default function ResultCard({ result }) {
  const ref = useRef(null);

  const downloadPdf = async () => {
    const canvas = await html2canvas(ref.current);
    const img = canvas.toDataURL('image/png');
    const doc = new jsPDF();
    doc.addImage(img, 'PNG', 10, 10, 190, 0);
    doc.save(`${result.register_number}-result.pdf`);
  };

  const downloadCertificate = async () => {
    const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/certificate/${result.short_code}`);
    const data = await res.json();
    const doc = new jsPDF('landscape');
    doc.setFontSize(20);
    doc.text(data.template?.title || 'Certificate of Achievement', 20, 20);
    doc.setFontSize(12);
    doc.text(`Name: ${result.student_name}`, 20, 40);
    doc.text(`Register No: ${result.register_number}`, 20, 50);
    doc.text(`School: ${result.school_name}`, 20, 60);
    doc.text(`Percentage: ${result.percentage}%`, 20, 70);
    doc.addImage(data.qrCode, 'PNG', 240, 20, 40, 40);
    doc.save(`${result.register_number}-certificate.pdf`);
  };

  return (
    <div ref={ref} className="glass rounded-2xl p-6 space-y-4 animate-float">
      <div className="flex gap-4 items-center">
        <img src={result.photo_url || 'https://placehold.co/90x90'} className="w-20 h-20 rounded-full object-cover" />
        <div>
          <h2 className="text-xl font-semibold">{result.student_name}</h2>
          <p>{result.school_name}</p>
          <p>{result.register_number}</p>
        </div>
      </div>
      <table className="w-full text-left text-sm">
        <tbody>
          {result.subjects_json.map((s) => (
            <tr key={s.name}><td>{s.name}</td><td>{s.mark}</td></tr>
          ))}
        </tbody>
      </table>
      <div className="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
        <div>Total: {result.total_marks}</div>
        <div>%: {result.percentage}</div>
        <div>Grade: {result.grade}</div>
        <div>Status: {result.status}</div>
      </div>
      <div className="flex gap-2">
        <button onClick={downloadPdf} className="px-4 py-2 rounded bg-cyan-600">Download Result PDF</button>
        <button onClick={downloadCertificate} className="px-4 py-2 rounded bg-emerald-600">Download Certificate</button>
      </div>
    </div>
  );
}
