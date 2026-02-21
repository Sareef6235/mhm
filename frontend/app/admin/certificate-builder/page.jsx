'use client';

import { useState } from 'react';
import api from '../../../lib/api';

export default function CertificateBuilder() {
  const [config, setConfig] = useState({
    exam_id: 1,
    logo_url: '',
    background_url: '',
    principal_name: '',
    signature_url: '',
    certificate_title: 'Certificate of Achievement',
    footer_text: 'Generated from ResultPro',
    layout_config: { name: { x: 45, y: 35 }, grade: { x: 50, y: 52 }, qr: { x: 82, y: 78 } }
  });

  return <button className="rounded-lg bg-indigo-500 px-3 py-2" onClick={() => api.post('/results/certificate-template', config)}>Save Certificate Template</button>;
}
