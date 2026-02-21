const sequelize = require('../config/database');

const Admin = require('./Admin')(sequelize);
const Exam = require('./Exam')(sequelize);
const Student = require('./Student')(sequelize);
const ResultLink = require('./ResultLink')(sequelize);
const CertificateTemplate = require('./CertificateTemplate')(sequelize);

Exam.hasMany(Student, { foreignKey: 'exam_id' });
Student.belongsTo(Exam, { foreignKey: 'exam_id' });
Exam.hasMany(ResultLink, { foreignKey: 'exam_id' });
ResultLink.belongsTo(Exam, { foreignKey: 'exam_id' });
Exam.hasOne(CertificateTemplate, { foreignKey: 'exam_id' });
CertificateTemplate.belongsTo(Exam, { foreignKey: 'exam_id' });

module.exports = { sequelize, Admin, Exam, Student, ResultLink, CertificateTemplate };
