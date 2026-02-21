const NodeCache = require('node-cache');

module.exports = new NodeCache({ stdTTL: 120, checkperiod: 150 });
