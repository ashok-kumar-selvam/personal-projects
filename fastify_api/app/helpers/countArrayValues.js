module.exports = function(array, key, value) {
  return array.reduce((count, object) => (object[key] == value ? count+1: count), 0);
}