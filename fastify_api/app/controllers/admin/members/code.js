const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    query: S.object().additionalProperties(false)
    .prop('expiresOn', S.number().default(0))
    .prop('limit', S.number().default(0)),

    response: {
      200: S.object().additionalProperties(false)
      .prop('code', S.string())
      .prop('name', S.string())
    }
  },
  
  async handler(req, res) {
    try {
      const {query} = req;
      let code;
      while(true) {
        code = Math.floor(Math.random()*89999999)+10000000;
        if(!await this.mongo.db.collection('shareCodes').findOne({code}))
          break;
        
      }
      const result = await this.mongo.db.collection('shareCodes').insertOne({
        admin_id: new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
        code: code,
        expiresOn: query.expiresOn,
        limit: query.limit,
        
      });
      
      return {code, name: req.user.name};
      
    } catch(error) {
      return res.send(error);
    }
  }
}