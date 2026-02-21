const { DataTypes } = require('sequelize');

module.exports = (sequelize) => sequelize.define('Exam', {
  id: { type: DataTypes.INTEGER.UNSIGNED, autoIncrement: true, primaryKey: true },
  title: { type: DataTypes.STRING(255), allowNull: false },
  total_marks: { type: DataTypes.INTEGER.UNSIGNED, allowNull: false },
  pass_mark: { type: DataTypes.INTEGER.UNSIGNED, allowNull: false },
  grading_config: { type: DataTypes.JSON, allowNull: false },
  is_active: { type: DataTypes.BOOLEAN, defaultValue: true }
}, {
  tableName: 'exams',
  underscored: true,
  createdAt: 'created_at',
  updatedAt: false
});
