module.exports = async function(req, res) {
  try {
    const {body} = req;
    const Users = this.mongo.db.collection('users');
    const {insertedId} = await Users.insertOne(body);
    const token = this.jwt.sign({
      uuid: insertedId,
      ...body
    });
    return res.send({token});

  } catch(error) {
    console.error(error);
    return res.status(400).send('error occured');
  }
}