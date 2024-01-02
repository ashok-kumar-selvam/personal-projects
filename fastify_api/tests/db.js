const { MongoClient } = require('mongodb');
const url = 'mongodb://localhost:27017/testing1';
const {MongoMemoryServer} = require('mongodb-memory-server');

async function connect() {
  try {
const mongod = await MongoMemoryServer.create({
instance: {

dbName: 'testingdb',
dbPath: './storage',
storageEngine: 'wiredTiger'
}
});
    const client = new MongoClient(mongod.getUri()+'testingdb', { useNewUrlParser: true, useUnifiedTopology: true });
    await client.connect();
    console.log('Connected to MongoDB');
    return client;
  } catch (err) {
    console.error('Error connecting to MongoDB', err);
    throw err;
  }
}

module.exports = { connect };
