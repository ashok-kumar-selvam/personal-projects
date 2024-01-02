const Questions = require('./Questions.json');
const Regex = require('./Regex');
const Users = require('./Users.json');

// this module will contain common settings and keys for the application.
module.exports= {
  //question type related methods
  getQuestionTypesAsArray: () => Object.keys(Questions.types).map(item => Questions.types[item].name),
  getQuestionTypeName: (key) => ((Object.keys(Questions.types).map(k => Questions.types[k]).find(obj=> obj.alias.includes(key.trim())) || {})['name'] || "not found"),
  getQuestionTypeKey: (value) => Object.keys(Questions.types).find(item => Questions.types[item].alias.includes(value)),
  getQuestionTypeNames: (...array) => Object.keys(Questions.types).filter(type => array.includes(type)).map(type => Questions.types[type].name),
  getSupportedQuestionTypesByEntity: (entity) => [].concat(...Object.keys(Questions.types).filter(key => Questions.types[key].entities.includes(entity)).map(type => Questions.types[type].alias)),
  getAllSupportedQuestionTypes: () => [].concat(...Object.keys(Questions.types).map(type => Questions.types[type].alias)),

  // regex related methods
  getRegex: (key) => Regex[key],
  testRegex: (string, regex) => (!Regex[regex] ? false: Regex[regex]?.test(string)),

  //user related methods
  getUserTypesAsArray: () => Object.keys(Users),
  getUserType: (type) => Users[type]?.name,
}
