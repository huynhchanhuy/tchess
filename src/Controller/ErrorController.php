<?php
 
namespace Tchess\Controller;
 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException;
 
class ErrorController
{
    public function exceptionAction(FlattenException $exception)
    {
        $response = json_encode(array(
            'code' => $exception->getCode(),
            'message' => 'Something went wrong! ('.$exception->getMessage().')',
        ));
 
        return new Response($response, $exception->getStatusCode());
    }
}
