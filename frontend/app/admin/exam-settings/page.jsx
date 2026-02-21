'use client';

import { useState } from 'react';
import api from '../../../lib/api';

export default function ExamSettings() {
  const [payload, setPayload] = useState({ title: '', total_marks: 600, pass_mark: 210, grading_config: [{ min: 90, grade: 'A+' }, { min: 80, grade: 'A' }, { min: 70, grade: 'B+' }] });

  return <div className="space-y-3"><input className="rounded-lg bg-white/10 p-2" placeholder="Exam title" onChange={(e) => setPayload({ ...payload, title: e.target.value })} /><button className="rounded-lg bg-indigo-500 px-3 py-2" onClick={() => api.post('/exams', payload)}>Save Exam Configuration</button></div>;
}
