const path = require('path');
const fp = require('fastify-plugin');

module.exports = fp(async function(fastify, options) {
  fastify.register(require('fastify-mailer'), {
    defaults: {from: 'support@riyozo.com'},
    transport: {
      host: 'smtp-relay.sendinblue.com',
      port: 587,
      secure: true,
      auth: {
        user: 'vendruvidu@gmail.com',
        pass: 'QzB8TbxK254v3XjZ',
      }
    },
    template: {
      engine: {handlebars: require('handlebars')}
    }
  });
  
  fastify.decorate('sendMail', async function({to,  template, ...context}) {
    const templatePath = path.join(__dirname, '..', 'templates', `${template}.hbs`);
    const emailOption = {
      to,
      subject: context.subject || `Message from ${process.env.APP_NAME}`,
      template: {
        file: templatePath,
        context: {...context, to}
      }
    };
    
    try {
      if(process.env.NODE_ENV == 'production') {
        const info = await fastify.mailer.send(emailOption);
        console.log(info);
        return true;
      } else if(process.env.NODE_ENV == 'development') {
        console.log(emailOption);
        return true;
      } else if(process.env.NODE_ENV == 'testing') {
        return true;
      }
    } catch(error) {
      console.error(error);
      return false;
    }
  })
});