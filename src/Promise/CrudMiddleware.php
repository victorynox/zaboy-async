<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @see http://tools.ietf.org/html/rfc2616#page-122
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stratigility\MiddlewareInterface;
use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Client;
use zaboy\async\Promise\PromiseException;
use zaboy\async\AsyncAbstract;

/**
 * Resolve GET POST PUT DELETE request
 *
 * @category   rest
 * @package    zaboy
 */
class CrudMiddleware extends AsyncAbstract implements MiddlewareInterface
{

    /**
     *
     * @var Store
     */
    public $store;

    /**
     *
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     *
     * @param Store $store
     * @throws PromiseException
     */
    public function __construct(Store $store)
    {
        parent::__construct();
        $this->store = $store;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $isPrimaryKeyValue = null !== $request->getAttribute('Primary-Key-Value');
        $httpMethod = $request->getMethod();
        try {
            switch ($httpMethod) {
                case $httpMethod === 'GET' && $isPrimaryKeyValue:
                    $response = $this->methodGetWithId($request, $response);
                    break;
                case $httpMethod === 'GET' && !($isPrimaryKeyValue):
                    throw new \zaboy\rest\RestException($httpMethod . ' method without Primary Key is not supported.');
                case $httpMethod === 'PUT' && $isPrimaryKeyValue:
                    $response = $this->methodPutWithId($request, $response);
                    break;
                case $httpMethod === 'PUT' && !($isPrimaryKeyValue):
                    throw new \zaboy\rest\RestException('PUT without Primary Key');
                case $httpMethod === 'POST' && $isPrimaryKeyValue:
                    throw new \zaboy\rest\RestException($httpMethod . ' method with Primary Key is not supported.');
                case $httpMethod === 'POST' && !($isPrimaryKeyValue):
                    $response = $this->methodPostWithoutId($request, $response);
                    break;
                case $httpMethod === 'DELETE':
                    throw new \zaboy\rest\RestException($httpMethod . ' method is not supported.');
                case $httpMethod === 'DELETE' && !($isPrimaryKeyValue):
                    throw new \zaboy\rest\RestException($httpMethod . ' method is not supported.');
                case $httpMethod === "PATCH":
                    throw new \zaboy\rest\RestException($httpMethod . ' method is not supported.');
                default:
                    throw new PromiseException(
                        'Method must be GET, PUT, POST or DELETE. '
                    . $request->getMethod() . ' given'
                    );
            }
        } catch (PromiseException $ex) {
            return new JsonResponse([
                $ex->getMessage()
                    ], 500);
        }

        if ($next) {
            return $next($this->request, $response);
        }
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \zaboy\async\Promise\PromiseException
     */
    public function methodGetWithId(ServerRequestInterface $request, ResponseInterface $response)
    {
        $primaryId = $request->getAttribute('Primary-Key-Value');
        if ($this->isId($primaryId)) {
            $promise = new Client($this->store, $primaryId);
            $promiseData = $promise->toArray();
            $this->request = $request->withAttribute('Response-Body', $promiseData);
            $response = $response->withStatus(200);
        } else {
            throw new PromiseException('There is no promise. PromiseId: ' . $primaryId);
        }
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \zaboy\rest\RestException
     * @internal param callable|null $next
     */
    public function methodPutWithId(ServerRequestInterface $request, ResponseInterface $response)
    {
        $primaryId = $request->getAttribute('Primary-Key-Value');
        if (!$this->isId($primaryId)) {
            throw new PromiseException('There is no promise. PromiseId: ' . $primaryId);
        }
        $promise = new Client($this->store, $primaryId);
        $promiseData = $request->getParsedBody();
        if (!isset($promiseData[Store::STATE])) {
            throw new PromiseException('There is no key STATE in the Body. PromiseId: ' . $primaryId);
        }
        if (!isset($promiseData[Store::RESULT])) {
            throw new PromiseException('There is no key RESULT in the Body. PromiseId: ' . $primaryId);
        }
        switch ($promiseData[Store::STATE]) {
            case PromiseInterface::FULFILLED:
                $promise->resolve($promiseData[Store::RESULT]);
                break;
            case PromiseInterface::REJECTED:

                $promise->reject($promiseData[Store::RESULT]);
                break;
            default:
                throw new PromiseException('The STATE field must be FULFILLED or REJECTED. PromiseId: ' . $primaryId);
        }

        $responseBody = $promise->toArray();
        $this->request = $request->withAttribute('Response-Body', $responseBody);
        $response = $response->withStatus(200);

        return $response;
    }

    /**
     *
     * Location: http://www.example.com/users/4/
     * http://www.restapitutorial.com/lessons/httpmethods.html
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \zaboy\rest\RestException
     * @internal param callable|null $next
     */
    public function methodPostWithoutId(ServerRequestInterface $request, ResponseInterface $response)
    {
        $promise = new Client($this->store);
        $responseBody = $promise->toArray();
        $this->request = $request->withAttribute('Response-Body', $responseBody);
        $response = $response->withStatus(201);
        $insertedPrimaryKeyValue = $promise->getId();
        $location = $request->getUri()->getPath();
        $response = $response->withHeader('Location', rtrim($location, '/') . '/' . $insertedPrimaryKeyValue);
        return $response;
    }

}
