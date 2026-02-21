import sanitizeHtml from 'sanitize-html';
import { pool } from '../config/db.js';
import { evaluateResult } from '../utils/calculation.js';
import { generateShortCode } from '../utils/shortLink.js';
import { resultSchema } from '../validators/resultValidator.js';

async function loadSettings() {
  const [rows] = await pool.query('SELECT * FROM exam_settings WHERE id=1 LIMIT 1');
  return rows[0] || { total_marks: 0, pass_mark: 0, grade_system: '[]' };
}

export async function createResult(req, res) {
  const payload = req.body;
  const { error, value } = resultSchema.validate(payload);
  if (error) return res.status(400).json({ message: error.message });

  const settings = await loadSettings();
  const calc = evaluateResult(value.subjects, settings);
  const shortCode = generateShortCode();

  const [insert] = await pool.query(
    `INSERT INTO results (register_number, student_name, school_name, photo_url, dob, subjects_json, total_marks, percentage, grade, status, short_code)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [
      sanitizeHtml(value.registerNumber),
      sanitizeHtml(value.name),
      sanitizeHtml(value.school),
      sanitizeHtml(value.photo || ''),
      value.dob,
      JSON.stringify(value.subjects),
      calc.total,
      calc.percentage,
      calc.grade,
      calc.status,
      shortCode
    ]
  );

  res.json({ id: insert.insertId, shortLink: `/r/${shortCode}` });
}

export async function bulkInsertResults(req, res) {
  const { data } = req.body;
  if (!Array.isArray(data) || !data.length) return res.status(400).json({ message: 'No data provided' });

  const settings = await loadSettings();
  let inserted = 0;

  for (const item of data) {
    const { error, value } = resultSchema.validate(item);
    if (error) continue;
    const calc = evaluateResult(value.subjects, settings);
    const shortCode = generateShortCode();

    await pool.query(
      `INSERT INTO results (register_number, student_name, school_name, photo_url, dob, subjects_json, total_marks, percentage, grade, status, short_code)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [value.registerNumber, value.name, value.school, value.photo || '', value.dob, JSON.stringify(value.subjects), calc.total, calc.percentage, calc.grade, calc.status, shortCode]
    );
    inserted += 1;
  }

  res.json({ inserted });
}

export async function searchResult(req, res) {
  const registerNumber = sanitizeHtml(req.query.registerNumber || '');
  const dob = req.query.dob;
  let sql = 'SELECT * FROM results WHERE register_number=? AND is_enabled=1';
  const params = [registerNumber];
  if (dob) {
    sql += ' AND dob=?';
    params.push(dob);
  }

  const [rows] = await pool.query(sql, params);
  const result = rows[0];

  await pool.query('INSERT INTO search_analytics (register_number, found) VALUES (?, ?)', [registerNumber, result ? 1 : 0]);

  if (!result) return res.status(404).json({ message: 'Result not found' });

  res.json({ ...result, subjects_json: JSON.parse(result.subjects_json || '[]') });
}

export async function getResultByShortCode(req, res) {
  const [rows] = await pool.query('SELECT * FROM results WHERE short_code=? AND is_enabled=1 LIMIT 1', [req.params.shortCode]);
  const row = rows[0];
  if (!row) return res.status(404).json({ message: 'Invalid short link' });
  return res.json({ ...row, subjects_json: JSON.parse(row.subjects_json || '[]') });
}

export async function listResults(req, res) {
  const [rows] = await pool.query('SELECT id, register_number, student_name, school_name, status, is_enabled, short_code, created_at FROM results ORDER BY id DESC');
  res.json(rows);
}

export async function toggleResult(req, res) {
  await pool.query('UPDATE results SET is_enabled = NOT is_enabled WHERE id=?', [req.params.id]);
  res.json({ message: 'Result state toggled' });
}

export async function deleteResult(req, res) {
  await pool.query('DELETE FROM results WHERE id=?', [req.params.id]);
  res.json({ message: 'Result deleted' });
}

export async function searchAnalytics(req, res) {
  const [rows] = await pool.query(
    'SELECT DATE(created_at) as day, COUNT(*) as count, SUM(found) as found_count FROM search_analytics GROUP BY DATE(created_at) ORDER BY day DESC LIMIT 30'
  );
  res.json(rows);
}
