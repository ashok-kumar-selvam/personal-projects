import Schemas from "../Utils/Schemas";
import Joi from 'joi';
import { Event } from "socket.io";

export default function([event, data]: any[], next: any) {
  try {
    
    
    if(!(event in Schemas))
      return next();

      
    const schemaName = event as keyof typeof Schemas;
    const schema = Schemas[schemaName];
    const {error, value} = schema.validate(data, {allowUnknown: false});
    
    if(error) {
      const errorMessage = error.details[0].message;
      
    return next(new Error(errorMessage));
  }

    
    next();
  } catch(error: any) {
    console.error(error);
    next(new Error('error occured'));
  }
}