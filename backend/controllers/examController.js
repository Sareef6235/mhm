const { Exam, Student, ResultLink } = require('../models');
const shortCode = require('../utils/shortCode');

const listExams = async (_req, res) => {
  const exams = await Exam.findAll({ order: [['created_at', 'DESC']] });
  return res.json(exams);
};

const createExam = async (req, res) => {
  const exam = await Exam.create(req.body);
  await ResultLink.create({ exam_id: exam.id, short_code: shortCode() });
  return res.status(201).json(exam);
};

const dashboardSummary = async (_req, res) => {
  const [totalStudents, totalExams, totalSearches, activeLinks] = await Promise.all([
    Student.count(),
    Exam.count(),
    Student.sum('search_count'),
    ResultLink.count({ where: { is_enabled: true } })
  ]);

  return res.json({ totalStudents, totalExams, totalSearches: totalSearches || 0, activeLinks });
};

module.exports = { listExams, createExam, dashboardSummary };
