const { DataTypes } = require('sequelize');

module.exports = (sequelize) => sequelize.define('CertificateTemplate', {
  id: { type: DataTypes.INTEGER.UNSIGNED, autoIncrement: true, primaryKey: true },
  exam_id: { type: DataTypes.INTEGER.UNSIGNED, allowNull: false },
  logo_url: DataTypes.STRING(500),
  background_url: DataTypes.STRING(500),
  principal_name: DataTypes.STRING(255),
  signature_url: DataTypes.STRING(500),
  certificate_title: DataTypes.STRING(255),
  footer_text: DataTypes.TEXT,
  layout_config: { type: DataTypes.JSON, allowNull: false }
}, {
  tableName: 'certificate_templates',
  underscored: true,
  timestamps: false
});
