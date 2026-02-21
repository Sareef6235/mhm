'use client';

import { useState } from 'react';
import api from '../../../lib/api';

export default function ImportGoogleSheetPage() {
  const [preview, setPreview] = useState([]);

  const demoPreview = async () => {
    const { data } = await api.post('/results/import/google-sheet/preview', {
      sheetRows: [{ reg: 'R1', name: 'A', dob: '2005-01-01', math: 90, sci: 88 }],
      columnMap: { register_number: 'reg', name: 'name', dob: 'dob', photo_url: 'photo', subjects: ['math', 'sci'] }
    });
    setPreview(data.preview);
  };

  return <div><button onClick={demoPreview} className="rounded-lg bg-indigo-500 px-3 py-2">Preview Mapping</button><pre className="mt-3 text-xs">{JSON.stringify(preview, null, 2)}</pre></div>;
}
