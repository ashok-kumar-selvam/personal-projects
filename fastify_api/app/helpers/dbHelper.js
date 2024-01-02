
const randomKey = () => {
  const min = 100000;
  const max = 999999;
  return Math.floor(Math.random()*(max-min+1))+min;
}

const checkKey = async (collection, key) => {
  const result = await collection.findOne({key});
  return Boolean(result);
}

const getKey = async (collection, to, seconds) => {
  if(!collection || !seconds || !to)
    throw new error(`The required values are not supplied for key generation.`);
  let key;
  do {
    key = randomKey();
  } while(await checkKey(collection, key));
  await collection.deleteMany({to});
  const result = await collection.insertOne({
    key: key,
    to: to,
    expire: (Date.now+(seconds*1000))
  });
  return key;
}

module.exports = {getKey};