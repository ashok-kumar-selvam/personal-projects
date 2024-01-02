const {MongoMemoryServer} = require('mongodb-memory-server');

module.exports = async function() {
  try {
    const environment = process.env.NODE_ENV;
    switch(environment) {
      case "production":
        return process.env.DATABASE_URL;
      break;
      case "development":
        return process.env.DATABASE_DEVELOPMENT_URL;
/*        return await MongoMemoryServer.create({
          instance: {
            dbName: 'testingdb',
            dbPath: './storage',
            storageEngine: 'wiredTiger'
          }
        }); */
      break;
      case "testing":
        return await MongoMemoryServer.create();

    }
  } catch(error) {
    throw error;
  }
}