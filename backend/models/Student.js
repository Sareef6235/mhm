const { DataTypes } = require('sequelize');

module.exports = (sequelize) => sequelize.define('Student', {
  id: { type: DataTypes.INTEGER.UNSIGNED, autoIncrement: true, primaryKey: true },
  exam_id: { type: DataTypes.INTEGER.UNSIGNED, allowNull: false },
  register_number: { type: DataTypes.STRING(80), allowNull: false },
  name: { type: DataTypes.STRING(200), allowNull: false },
  dob: { type: DataTypes.DATEONLY, allowNull: true },
  photo_url: { type: DataTypes.STRING(500), allowNull: true },
  subjects: { type: DataTypes.JSON, allowNull: false },
  total: { type: DataTypes.FLOAT, allowNull: false },
  percentage: { type: DataTypes.FLOAT, allowNull: false },
  grade: { type: DataTypes.STRING(10), allowNull: false },
  result_status: { type: DataTypes.ENUM('PASS', 'FAIL'), allowNull: false },
  search_count: { type: DataTypes.INTEGER.UNSIGNED, defaultValue: 0 }
}, {
  tableName: 'students',
  underscored: true,
  createdAt: 'created_at',
  updatedAt: false,
  indexes: [
    { fields: ['register_number'] },
    { fields: ['exam_id'] },
    { fields: ['result_status'] }
  ]
});
