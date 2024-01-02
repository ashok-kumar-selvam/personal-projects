import Socket from "../Interfaces/Socket";

export default async function(socket: Socket) {
  socket.on('info', async () => {
    try  {
      if(!socket.isValidated)
        return socket.emit('validation_error', 'Invalid request. The request is not validated. ');

      return socket.emit('info', socket.validatedData);
    } catch(error:any) {
      console.error(error);
      return socket.emit('error', 'Unexpected error occured')
    }
  })
}