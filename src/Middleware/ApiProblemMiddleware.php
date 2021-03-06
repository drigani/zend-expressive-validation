<?php
namespace ExpressiveValidator\Middleware;

use Doctrine\Tests\Common\Cache\ApcCacheTest;
use ExpressiveValidator\Validator\ValidationFailedException;
use LosMiddleware\ApiProblem\Model\ApiProblem;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stratigility\ErrorMiddlewareInterface;

final class ApiProblemMiddleware implements ErrorMiddlewareInterface
{

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     */
    public function __invoke($error, ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $data = [];

        $status = $this->getStatusCode($error, $response);
        $message = $this->getMessage($error, $request, $response);
        $additionalDetails = $this->getAdditionalDetails($error);

        if ($status == 404 && empty($message)) {
            $detail = sprintf("Path '%s' not found.", $request->getUri()->getPath());
        } else {
            $detail = $message;
        }

        $problem = new ApiProblem($status, $detail, null, null, $additionalDetails);

        $data = $problem->toArray();

        $requestId = $this->getRequestId($request, $response);
        if (! empty($requestId)) {
            $data['code'] = $requestId;
        }

        $response = new JsonResponse($data, $data['status'], $response->getHeaders());
        return $response->withHeader('Content-Type', 'application/problem+json');
    }

    /**
     * Returns an error message from $error
     *
     * @param \Exception $error
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return string
     */
    private function getMessage($error, ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($error instanceof \Exception) {
            return $error->getMessage();
        }
        return 'An error ocurred.';
    }

    /**
     * Returns the status code from the error or response
     *
     * @param unknown $error
     * @param ResponseInterface $response
     * @return int
     */
    private function getStatusCode($error, ResponseInterface $response)
    {
        if ($error instanceof \Exception && ($error->getCode() >= 400 && $error->getCode() <= 599)) {
            return $error->getCode();
        }

        $status = $response->getStatusCode();
        if (! $status || $status < 400 || $status > 599) {
            $status = 500;
        }
        return $status;
    }

    /**
     * Returns the X-Request-Id if present
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return string
     */
    private function getRequestId(RequestInterface $request, ResponseInterface $response)
    {
        if ($request->hasHeader('X-Request-Id')) {
            return $request->getHeader('X-Request-Id')[0];
        }

        if ($response->hasHeader('X-Request-Id')) {
            return $response->getHeader('X-Request-Id')[0];
        }

        return '';
    }

    /**
     * Returns additional Details info in case validation Failed exception
     *
     * @param $error
     * @return array
     */
    private function getAdditionalDetails($error)
    {

        if ($error instanceof ValidationFailedException) {
            return [
                'error' => $error->getValidationResult()->getMessages()
            ];
        }

        return [];
    }
}
