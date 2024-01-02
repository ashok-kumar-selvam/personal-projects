<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use \Firebase\JWT\JWT;
use Config\Services;


class MemberAuth implements FilterInterface
{

    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */

    public function before(RequestInterface $request, $arguments = null)
    {
helper('Auth');


$response = Services::response();
$key = getAuthKey();

$header = $request->getServer('HTTP_AUTHORIZATION');
if(!$header)  {
//header("Access-Control-Allow-Headers: X-API-KEY, Origin,X-Requested-With, Content-Type, Accept, Access-Control-Requested-Method, Access-Control-Allow-Origin, Authorization");
//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, PUT, DELETE");
return $response->setJSON(['message' => 'Token Required'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);

}

$token = explode(' ', $header)[1];
try {
$user = JWT::decode($token, $key, ['HS256']);

if(!$user) {
return $response->setJson([
'status' => 'reject',
'message' => 'invalid access'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
} else if($user->type != 'member') {
return $response->setJson([
'status' => 'reject',
'message' => 'Member can only access this resource.'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
} else if($user->status == 'pending') {

return $response->setJson([
'status' => 'pending',
'message' => 'Member can only access this resource.'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
}

$request->user = $user;

} catch (\Throwable $th) {
//header("Access-Control-Allow-Headers: X-API-KEY, Origin,X-Requested-With, Content-Type, Accept, Access-Control-Requested-Method, Access-Control-Allow-Origin, Authorization");
//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, PUT, DELETE");
return $response->setJSON(['message' => $th->getMessage().'Invalid Token'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);


}

    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null$arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
//
    }
}
