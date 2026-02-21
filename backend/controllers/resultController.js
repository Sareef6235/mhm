const QRCode = require('qrcode');
const { Op } = require('sequelize');
const { Exam, Student, ResultLink, CertificateTemplate } = require('../models');
const { buildComputedResult } = require('../utils/resultCalculator');
const shortCode = require('../utils/shortCode');
const cache = require('../utils/cache');

const searchResult = async (req, res) => {
  const { register_number, exam_id, dob } = req.query;
  const cacheKey = `${exam_id}-${register_number}-${dob || ''}`;
  const cached = cache.get(cacheKey);
  if (cached) return res.json(cached);

  const where = { exam_id, register_number };
  if (dob) where.dob = dob;

  const student = await Student.findOne({ where, include: [{ model: Exam }] });
  if (!student) return res.status(404).json({ message: 'Result not found' });

  await student.increment('search_count');

  const verificationUrl = `${process.env.CLIENT_ORIGIN}/verify-certificate?student=${student.id}`;
  const qrDataUrl = await QRCode.toDataURL(verificationUrl);

  const payload = { student, verificationUrl, qrDataUrl };
  cache.set(cacheKey, payload);
  return res.json(payload);
};

const bulkUploadStudents = async (req, res) => {
  const { exam_id, rows } = req.body;
  const exam = await Exam.findByPk(exam_id);
  if (!exam) return res.status(404).json({ message: 'Exam not found' });

  const normalized = rows.map((row) => ({
    exam_id,
    register_number: row.register_number,
    name: row.name,
    dob: row.dob || null,
    photo_url: row.photo_url || null,
    subjects: row.subjects,
    ...buildComputedResult({
      subjects: row.subjects,
      totalMarks: exam.total_marks,
      passMark: exam.pass_mark,
      gradingConfig: exam.grading_config
    })
  }));

  await Student.bulkCreate(normalized);
  const link = await ResultLink.create({ exam_id, short_code: shortCode() });

  return res.status(201).json({ inserted: normalized.length, link });
};

const getShortLink = async (req, res) => {
  const record = await ResultLink.findOne({ where: { short_code: req.params.shortcode, is_enabled: true } });
  if (!record) return res.status(404).json({ message: 'Short link not found' });

  return res.json({ exam_id: record.exam_id, redirect: `/result/${record.exam_id}` });
};

const manageLinks = async (_req, res) => {
  const links = await ResultLink.findAll({ include: [{ model: Exam, attributes: ['id', 'title'] }] });
  return res.json(links);
};

const toggleLink = async (req, res) => {
  const link = await ResultLink.findByPk(req.params.id);
  if (!link) return res.status(404).json({ message: 'Link not found' });
  link.is_enabled = !link.is_enabled;
  await link.save();
  return res.json(link);
};

const saveCertificateTemplate = async (req, res) => {
  const [template] = await CertificateTemplate.upsert(req.body, { returning: true });
  return res.json(template);
};

const verifyCertificate = async (req, res) => {
  const student = await Student.findByPk(req.query.student, { include: [{ model: Exam }] });
  if (!student) return res.status(404).json({ message: 'Invalid certificate' });

  const template = await CertificateTemplate.findOne({ where: { exam_id: student.exam_id } });
  return res.json({ valid: true, student, template });
};

const importFromGoogleSheet = async (req, res) => {
  const { sheetRows, columnMap } = req.body;
  const mapped = sheetRows.map((row) => {
    const subjects = Object.fromEntries((columnMap.subjects || []).map((key) => [key, Number(row[key] || 0)]));
    return {
      register_number: row[columnMap.register_number],
      name: row[columnMap.name],
      dob: row[columnMap.dob],
      photo_url: row[columnMap.photo_url],
      subjects
    };
  });

  return res.json({ preview: mapped.slice(0, 20), rows: mapped });
};

module.exports = {
  searchResult,
  bulkUploadStudents,
  getShortLink,
  manageLinks,
  toggleLink,
  saveCertificateTemplate,
  verifyCertificate,
  importFromGoogleSheet
};
