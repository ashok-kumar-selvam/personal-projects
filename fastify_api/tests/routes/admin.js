const members = require('../controllers/admin/members');
const quizzes = require('../controllers/admin/Quizzes');

module.exports = async function(t) {
  try {
    //t.test('members.create', members.create);
    //t.test('members.update', members.update);

    t.test(quizzes.create);
  } catch(error) {
    throw error;
  }
}