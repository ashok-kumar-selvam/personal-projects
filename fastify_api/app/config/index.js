const messages = require('./Messages.json');
const database = require('./Database');
const regex = require('./Regex');

module.exports = {

  getMessage: code => {
    if(!messages[code])
      return 'Unexpected error. ';
    return messages[code];
  },

  getRegex: code => {
    if(!regex[code])
      throw new Error("Unknown regex request");
    return regex[code];
  },
  getDb: database,
  
}