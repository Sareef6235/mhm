require('dotenv').config();
const app = require('./app');
const { sequelize } = require('./models');

const port = process.env.PORT || 5000;

(async () => {
  try {
    await sequelize.authenticate();
    console.log('Database connected');
    app.listen(port, () => console.log(`Server listening on port ${port}`));
  } catch (error) {
    console.error('Unable to start server:', error.message);
    process.exit(1);
  }
})();
