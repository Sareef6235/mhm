import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import { pool } from '../config/db.js';
import { env } from '../config/env.js';

export async function login(req, res) {
  const { username, password } = req.body;
  const [rows] = await pool.query('SELECT id, username, password_hash FROM admins WHERE username=? LIMIT 1', [username]);
  const admin = rows[0];

  if (!admin) return res.status(401).json({ message: 'Invalid credentials' });

  const valid = await bcrypt.compare(password, admin.password_hash);
  if (!valid) return res.status(401).json({ message: 'Invalid credentials' });

  const token = jwt.sign({ id: admin.id, username: admin.username }, env.jwtSecret, { expiresIn: env.jwtExpiresIn });
  return res.json({ token, admin: { id: admin.id, username: admin.username } });
}
