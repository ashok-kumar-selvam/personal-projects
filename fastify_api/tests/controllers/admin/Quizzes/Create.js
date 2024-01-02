module.exports = async function(t) {
  try {
    const {app, config} = t.context;
    const headers = config.getHeader();
    const [method, url] = ['POST', '/admin/quizzes'];
    const payload = {};

    const unauthorised = await app.inject({method, url, payload});
    t.equal(unauthorised.statusCode, 401, 'The request should be rejected because of missing bearer token. ');

  } catch(error) {
    console.error(error);
    throw new Error(error);
  }
}