import { pool } from '../config/db.js';

export async function getSettings(req, res) {
  const [rows] = await pool.query('SELECT * FROM exam_settings ORDER BY id DESC LIMIT 1');
  if (!rows[0]) return res.json({ total_marks: 0, pass_mark: 0, grade_system: [] });

  const row = rows[0];
  return res.json({ ...row, grade_system: JSON.parse(row.grade_system || '[]') });
}

export async function upsertSettings(req, res) {
  const { total_marks, pass_mark, grade_system } = req.body;
  await pool.query(
    `INSERT INTO exam_settings (id, total_marks, pass_mark, grade_system)
     VALUES (1, ?, ?, ?)
     ON DUPLICATE KEY UPDATE total_marks=VALUES(total_marks), pass_mark=VALUES(pass_mark), grade_system=VALUES(grade_system)`,
    [total_marks, pass_mark, JSON.stringify(grade_system || [])]
  );
  return res.json({ message: 'Settings updated' });
}

export async function getCertificateConfig(req, res) {
  const [rows] = await pool.query('SELECT * FROM certificate_templates WHERE id=1');
  if (!rows[0]) return res.json({});
  const row = rows[0];
  return res.json({
    ...row,
    fields_json: JSON.parse(row.fields_json || '[]')
  });
}

export async function upsertCertificateConfig(req, res) {
  const { title, footer, logo_url, principal_name, signature_url, background_url, watermark, fields_json } = req.body;
  await pool.query(
    `INSERT INTO certificate_templates (id, title, footer, logo_url, principal_name, signature_url, background_url, watermark, fields_json)
     VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
     title=VALUES(title), footer=VALUES(footer), logo_url=VALUES(logo_url), principal_name=VALUES(principal_name),
     signature_url=VALUES(signature_url), background_url=VALUES(background_url), watermark=VALUES(watermark), fields_json=VALUES(fields_json)`,
    [title, footer, logo_url, principal_name, signature_url, background_url, watermark, JSON.stringify(fields_json || [])]
  );
  return res.json({ message: 'Certificate template saved' });
}
