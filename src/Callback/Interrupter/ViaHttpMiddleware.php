<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @see http://tools.ietf.org/html/rfc2616#page-122
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Callback\Interrupter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use zaboy\async\AsyncAbstract;
use zaboy\async\Callback\CallbackException;
use zaboy\async\Promise\Client;
use zaboy\async\Promise\Store;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stratigility\MiddlewareInterface;

/**
 * Resolve GET POST PUT DELETE request
 *
 * @category   rest
 * @package    zaboy
 */
class ViaHttpMiddleware extends AsyncAbstract implements MiddlewareInterface
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

        if ($httpMethod === 'POST' && !($isPrimaryKeyValue)) {
            try {
                $response = $this->methodPostWithoutId($request, $response);
            } catch (PromiseException $ex) {
                return new JsonResponse([
                    $ex->getMessage()
                ], 500);
            }
        } else {
            throw new CallbackException(
                'Method must be GET, PUT, POST or DELETE. '
                . $request->getMethod() . ' given'
            );
        }

        $this->request = is_null($this->request) ? $request : $this->request;
        if ($next) {
            return $next($this->request, $response);
        }
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
        $body = $request->getBody();

        $data = unserialize(base64_decode($body));
        $promiseId = null;
        $callback = null;
        $value = null;

        extract($data); // $promiseId $callback $value
        $promise = new Client($this->store, $promiseId);

        /** @var callable $callback */
        $callback($value, $promise);
        $response = $response->withStatus(201);
        $insertedPrimaryKeyValue = $promiseId;
        $location = $request->getUri()->getPath();
        $response = $response->withHeader('Location', rtrim($location, '/') . '/' . $insertedPrimaryKeyValue);
        $responseBody = $promise->getId();
        $this->request = $request->withAttribute('Response-Body', $responseBody);
        return $response;
    }

}
