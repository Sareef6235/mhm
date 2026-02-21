const { DataTypes } = require('sequelize');

module.exports = (sequelize) => sequelize.define('ResultLink', {
  id: { type: DataTypes.INTEGER.UNSIGNED, autoIncrement: true, primaryKey: true },
  exam_id: { type: DataTypes.INTEGER.UNSIGNED, allowNull: false },
  short_code: { type: DataTypes.STRING(10), allowNull: false, unique: true },
  is_enabled: { type: DataTypes.BOOLEAN, defaultValue: true }
}, {
  tableName: 'result_links',
  underscored: true,
  createdAt: 'created_at',
  updatedAt: false
});
