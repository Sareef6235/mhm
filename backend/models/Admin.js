const { DataTypes } = require('sequelize');

module.exports = (sequelize) => sequelize.define('Admin', {
  id: { type: DataTypes.INTEGER.UNSIGNED, autoIncrement: true, primaryKey: true },
  username: { type: DataTypes.STRING(100), allowNull: false },
  email: { type: DataTypes.STRING(150), allowNull: false, unique: true },
  password: { type: DataTypes.STRING(255), allowNull: false }
}, {
  tableName: 'admins',
  underscored: true,
  createdAt: 'created_at',
  updatedAt: false
});
