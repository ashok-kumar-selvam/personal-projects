require('dotenv').config();
const t  = require('tap');
process.env.NODE_ENV = 'testing';
const initial = require('../app');
const app = require('fastify')({logger: {level: 'error'}});
app.register(initial);
const config = require('../app/config');
const tokenHelper = require('./helpers/tokenHelper');
const common = require('./routes/common');
const admin = require('./routes/admin');

t.beforeEach(t => (t.context = {app, config: {...config, ...tokenHelper}}));
t.teardown((t) => app.close());
t.test('common', common);
t.test('admin', admin);