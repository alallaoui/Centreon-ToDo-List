<?php

namespace App\Traits;

use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Traits ControllerTrait
 *  @method JsonResponse jsonOkResponse($data, $context = [], $headers = []) returns 200 (success) Json response.
 *  @method JsonResponse jsonCreatedResponse($data, $context = [], $headers = [])  returns 201 (created) Json response.
 *  @method JsonResponse jsonNoContentResponse($data, $context = [], $headers = [])  returns 201 (created) Json response.
 *  @method JsonResponse jsonBadRequestResponse($data, $context = [], $headers = []) returns 400 (bad request) Json response.
 *  @method JsonResponse jsonForbiddenResponse($data, $context = [], $headers = []) returns 403 (forbidden) Json response.
 *  @method JsonResponse jsonNotFoundResponse($data, $context = [], $headers = []) returns 404 (not found) Json response.
 *  @method JsonResponse jsonConflictResponse($data, $context = [], $headers = []) returns 409 (conflict) Json response.
 *  @method JsonResponse jsonInternalServerErrorResponse($data, $context = [], $headers = []) returns 500 (Internal server error) Json response.
 */
trait ControllerTrait
{
    /**
     * returns JsonResponse depending on $responseStatus string (OK = 200, NotFound = 404, Created = 201).
     *
     * @param $responseStatus
     * @param $data
     * @param array $context
     * @param array $headers
     * @return JsonResponse
     */
    protected function jsonResponse(
        $responseStatus,
        $data,
        array $context = [],
        array $headers = ['Content-Type' => 'application/json']
    ) {
        $responseStatus = strtoupper(preg_replace('/(?<!^)[A-Z]/', '_$0', $responseStatus));
        $httpStatus = Response::class.'::HTTP_'.$responseStatus;
        if (defined($httpStatus)) {
            if (is_string($data)) {
                // convert response body in ['message' => 'description'] format
                $data = $this->msg($data);
            } elseif ($data === false){
                $data = [];
            }

            return $this->json($data, constant($httpStatus), $headers, $context);
        } else {
            throw new \BadMethodCallException(
                "Undefined method json".ucwords(strtolower($responseStatus))."Response. 
                The method name format is json{responseStatus}Response {responseStatus} is the name of
                the constant in".Response::class."without HTTP_ prefix and lowerCamelCased
                Example :
                  - HTTP_OK = > jsonOkResponse
                  - HTTP_CREATED => jsonCreatedResponse
                  - HTTP_BAD_REQUEST => jsonBadRequestResponse"
            );
        }
    }

    /**
     * @param $method
     * @param $arguments
     * @return JsonResponse
     */
    public function __call($method, $arguments)
    {
        if (preg_match('/^json(.*)Response$/', $method, $responseStatus)) {
            return $this->jsonResponse($responseStatus[1], ...$arguments);
        }
    }

    /**
     * format $description to ['message' => $description]
     * @param string $description
     * @return array
     */
    protected function msg(string $description)
    {
        return ['message' => $description];
    }


    /**
     * @param Request $request
     * @param string $paramName
     * @param string $defaultValue
     * @return string
     */
    protected function getJsonParam(Request $request, string $paramName, string $defaultValue = null)
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return $data[$paramName] ?? $defaultValue;
    }

}
