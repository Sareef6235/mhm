import QRCode from 'qrcode';
import { env } from '../config/env.js';
import { pool } from '../config/db.js';

export async function certificateData(req, res) {
  const [rows] = await pool.query('SELECT * FROM results WHERE short_code=? LIMIT 1', [req.params.shortCode]);
  if (!rows[0]) return res.status(404).json({ message: 'Certificate data not found' });

  const result = rows[0];
  const verificationUrl = `${env.clientUrl}/verify/${result.short_code}`;
  const qrCode = await QRCode.toDataURL(verificationUrl);

  const [tplRows] = await pool.query('SELECT * FROM certificate_templates WHERE id=1 LIMIT 1');
  const tpl = tplRows[0] || null;

  res.json({
    result: { ...result, subjects_json: JSON.parse(result.subjects_json || '[]') },
    template: tpl ? { ...tpl, fields_json: JSON.parse(tpl.fields_json || '[]') } : null,
    verificationUrl,
    qrCode
  });
}
