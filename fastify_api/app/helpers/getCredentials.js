module.exports = async function(name, collection) {
  try {
    name = name.substring(0, 4);
    let iteration = 0;
    let userId;
    
    while(true) {
      let number = iteration < 2000 ? Math.floor(Math.random()*9000)+1000: Math.floor(Math.random()*90000)+10000;
      
      userId = name+number;
      
      if(!await collection.findOne({userId}))
        break;
      
      iteration++;
      
    }
    const password = Math.random().toString(36).slice(-8);
    return {userId, password};
    
  } catch(error) {
    console.log(error);
    throw new Error(error);
  }
}