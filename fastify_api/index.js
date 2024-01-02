require('dotenv').config();
const fastify = require('fastify')({logger: {level: 'info'}});
const app = require('./app.js');
fastify.register(app);

const start = async () => {
  try {
    
    
    await fastify.listen({port: 3003});
    
  } catch(error) {
    fastify.log.error(error);
  }
}
start();


// Graceful shutdown
process.on('SIGINT', async () => {
  try {
    await fastify.close();
    console.log('Server shutting down');
    process.exit(0);
  } catch(error) {
    console.error(error);
    process.exit(1);
  }
});