<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use Illuminate\Support\Facades\Response;


/**
 * Provides a way to generate responses for all typical cases
 * such as NotFound, InternalError, etc.
 *
 * Class ApiController
 * @package App\Http\Controllers
 */
class ApiController extends Controller
{
    /**
     * If this variable is not empty, API response will display errors instead of data.
     *
     * @var array
     */
    protected $requestErrors = [];

    /**
     * If custom HTTP response code is not defined for an entry within the $errorList, this value will be used.
     *
     * @var int
     */
    protected $defaultErrorResponseCode = 500;

    /**
     * For brevity (and reading speed), each error is defined as
     * $internalErrorCode => ['Error Description', OptionalHttpResponseCodeThatOverridesDefaultErrorResponseCode].
     *
     * @var array
     */
    protected $errorList = [
        10 => ['Generic error'],
        11 => ['User is not authenticated', 401],
        12 => ['Wrong HTTP method type - most likely you are using GET instead of POST', 501],
        13 => ['This API Method does not exist', 404],
    ];
    /**
     * Intentionally empty - controller that inherits from this class is supposed to define it's own errors.
     *
     * @var array
     */
    protected $controllerErrors = [];

    /**
     * In the future, this might need to be refactored to have getters/setters.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Main purpose of having a custom controller is to prepare a unified errorList to be used in specific controller.
     */
    public function __construct()
    {
        // Adding Fractal support for optional data transformations
        $this->fractal = new Manager();

        // Let's add whatever errors controller might have defined to whatever errors are inherited from parent class
        $this->errorList += $this->controllerErrors;
    }


    /**
     * Call it to when an error is encountered.
     *
     * @param $errorCode
     * @return $this
     * @throws \Exception
     */
    public function setError($errorCode)
    {
        if (!isset($this->errorList[$errorCode])) {
            // Preventing some really elaborate XSS attack by casting the error code to (int) - it would be
            // nice to prevent a non-integer in the first place via argument type hinting, but bc PHP:
            // http://stackoverflow.com/questions/5430126/how-to-force-arguments-to-be-integer-string
            throw new \Exception('Error code ' . (int)$errorCode . ' is not defined');
        }
        $error = $this->errorList[$errorCode];

        $this->requestErrors[] = [
            'code' => $errorCode,
            'details' => $error[0],
        ];

        $this->statusCode = isset($error[1]) ? $error[1] : $this->defaultResponseCode;

        return $this;
    }


    /**
     * Generates API response structure with appropriate meta headers and (optionally) Fractal transformations.
     *
     * @param array $data
     * @param int $statusCode
     * @param array $headers
     * @return mixed
     */
    public function respond($data = [], $statusCode = 200, $headers = [])
    {

        // If supplied data has any associated transformers, apply them
        $data = $this->applyTransformations($data);

        if (!isset($this->statusCode)) {
            $this->statusCode = $statusCode;
        }

        $output = [
            'meta' => [
                // In case debugging is done in a simple browser without e.g. postman plugin for Chrome,
                // the code is still easily visible

                'http_code' => $this->statusCode,
                // Handy to keep track of which routes need more optimization - also useful when testing
                // remotely to quickly remove network transfer time out of the consideration

                'duration' => round(microtime(true) - LARAVEL_START, 4),
                // Used RAML as per http://www.mikestowe.com/2014/07/raml-vs-swagger-vs-api-blueprint.php
                // If the project would actually grow into something that would have lots of methods,
                // switching to Swagger *might* make sense

                'documentation' => url() . '/documentation/index.php?path=' . $this->getDocumentationEndpoint(),

            ],
        ];

        if (count($this->requestErrors) > 0) {
            $output['errors'] = $this->requestErrors;
        } else {
            $output['data'] = $data;
        }

        // This would need to be refactored if content negotation is added
        return Response::json($output, $this->statusCode, $headers);
    }


    /**
     * A generic way to respond with error - use it when defining custom error responses.
     *
     * @param int $errorCode
     * @return mixed
     * @throws \Exception
     */
    public function respondWithError($errorCode = 10)
    {
        return $this->setError($errorCode)->respond();
    }


    /**
     * Called when API method which requires authentication is called without it.
     *
     * @return mixed
     */
    public function respondWhenUnauthenticated()
    {
        return $this->respondWithError(11);
    }


    /**
     * Called when API method is called using a wrong HTTP code.
     *
     * @return mixed
     */
    public function respondWhenBadMethod()
    {
        return $this->respondWithError(12);
    }

    /**
     * Called when request can not be routed.
     *
     * @return mixed
     */
    public function respondWhenNotImplemented()
    {
        return $this->respondWithError(13);
    }

    /**
     * Determine documentation endpoint to link to.
     *
     * @return string mixed
     */
    protected function getDocumentationEndpoint()
    {
        $endpoint = \Route::getCurrentRoute();
        if ($endpoint == null) { // Request was not routed properly
            return '/'; // So the best we can do is just go to the root of documentation
        }

        // Otherwise, let's direct to the documentation for the route that was used
        $endpoint = preg_replace('#.*v[0-9]*#', '', $endpoint->getPath());
        return $endpoint;
    }

    /**
     * Call this to run (if it exists) data transformer based.
     *
     * @param $data
     * @return mixed
     */
    protected function applyTransformations($data)
    {
        if (is_object($data)) {

            // If we have a collection, let's assume that all objects within it are of the same class
            $dataClassName = (method_exists($data,
                    'first') && is_object($data->first())) ? get_class($data->first()) : get_class($data);

            // And it's elements are coming from app models
            $dataClassPath = explode('\\', $dataClassName);
            if (isset($dataClassPath[1])) {

                // All transformer classes are expected to be placed with app/Transformers
                $transformerClass = "\\App\\Transformers\\" . $dataClassPath[1] . "Transformer";

                // And a transformer for a said model exists
                if (class_exists($transformerClass)) {
                    return $this->transformData($data, new $transformerClass);
                }
            }
        }
        return $data;
    }


    /**
     * Called by applyTransformations() if a transformer is found - will determine if data needs to be transformed
     * as a collection or as an item and will transform it using Fractal.
     *
     * @param $data
     * @param $callback
     * @return mixed
     */
    public function transformData($data, $callback)
    {
        if (get_class($data) == 'Illuminate\Database\Eloquent\Collection') {
            $resource = new Collection($data, $callback);
        } else {
            $resource = new Item($data, $callback);
        }

        return $this->fractal->createData($resource)->toArray()['data'];
    }

}
