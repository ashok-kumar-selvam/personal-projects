const {MongoMemoryServer} = require('mongodb-memory-server');
const config = require('./app/config');


module.exports = async function(fastify, opts) {
    fastify.register(require('@fastify/caching')); // do not forgot to add {useAwait: true} in @fastify/caching folder in node_modules folder
    fastify.register(require('@fastify/cors'));
    fastify.register(require('@fastify/jwt'), {secret: process.env.JWT_SECRET});
    fastify.register(require('./app/plugins/sendMail'));

    fastify.register(require('@fastify/multipart'), {
        attachFieldsToBody: 'keyValues',
        onFile: async (part) => {
            const buff = await part.toBuffer();
            part.value = {data: buff, filename: part.filename, mimetype: part.mimetype}
        }
    });

    if(process.env.NODE_ENV == 'testing') {
        console.log("The temporary database is running");
        const db = await config.getDb();
        process.env.DATABASE_URL= db.getUri()+'testingdb';
        fastify.addHook('onClose', async function(req, res) {
            db.stop();
        });
        
        
    }

    fastify.register(require('@fastify/mongodb'), {
        forceClose: true,
        url: process.env.NODE_ENV == 'development' ? process.env.DATABASE_DEVELOPMENT_URL: process.env.DATABASE_URL
    })
    
    
    //fastify.register(require('@fastify/mongodb'), {
        //forceClose: true,
        //url: process.env.DATABASE_URL
    //});

    fastify.decorate('config', require('./app/config'));

    
    fastify.register(require('./app/routes/common'));
    fastify.register(require('./app/routes/admin'), {prefix: '/admin'});
    fastify.register(require('./app/routes/member'));
    
}