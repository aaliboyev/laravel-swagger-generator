<?php

namespace Aaliboyev\Lod\Services;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Aaliboyev\Lod\Contracts\OpenApiGenerator as OpenApiGeneratorContract;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;


class OpenApiGenerator implements OpenApiGeneratorContract{
    protected string $apiPrefix;

    public function __construct($apiPrefix)
    {
        // Retrieve API prefix from configuration
        $this->apiPrefix = $apiPrefix;
    }

    // Method to retrieve and group all API routes
    public function getGroupedRoutes()
    {
        $routes = collect(Route::getRoutes());
        $apiRoutes = $routes->filter(function ($route) {
            // Only include routes that are within the 'api' middleware group
            return Str::startsWith($route->uri(), $this->apiPrefix);
        });

        $groupedRoutes = $apiRoutes->mapToGroups(function ($route) {
            // Remove the prefix from the URI, then get the first segment
            $strippedUri = Str::after($route->uri(), $this->apiPrefix . '/');
            $firstSegment = explode('/', $strippedUri)[0];
            return [$firstSegment ?: 'misc' => $route]; // 'misc' is a default fallback group
        });

        return $groupedRoutes;
    }

    // Method to generate OpenAPI schema JSON
    public function generateOpenApiSchema($groupedRoutes)
    {
        // Basic OpenAPI structure
        $openapi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Sample API',
                'description' => 'This is a sample OpenAPI document.',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => 'http://localhost/api', 'description' => 'Local server'],
                // Additional servers can go here
            ],
            'paths' => $this->generatePaths($groupedRoutes),
            'components' => [
                // Reusable components like schemas, responses, and parameters will be defined here
            ],
        ];

        return $openapi;
    }

    // Additional methods to generate the paths, components, etc., can go here
    public function generatePaths($groupedRoutes)
    {
        $paths = [];

        foreach ($groupedRoutes as $group => $routes) {
            $tag = ucfirst($group);
            foreach ($routes as $route) {
                // Normalize the method to lowercase
                $method = strtolower($route->methods()[0]);

                // Skip over unconventional or non-RESTful HTTP methods
                if (!in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
                    continue;
                }

                // Get the URI and strip any potential trailing slashes
                $uri = '/' . trim(Str::after($route->uri(), $this->apiPrefix), '/');

                // Initialize the path if not already set
                if (!isset($paths[$uri])) {
                    $paths[$uri] = [];
                }

                // Build the operation object for the route
                $operation = [
                    'tags' => [$tag],
                    'responses' => [
                        // Each HTTP status code must be mapped to a description
                        '200' => [
                            'description' => 'OK',
                            // Example, schema, or reference can be added here
                        ],
                        // More responses can be added to handle different HTTP status codes
                    ],
                    // Description, parameters, requestBody, etc., can be added here
                ];
                $parameters = $this->getRequestParameters($route);
                if (!empty($parameters)) {
                    $operation['requestBody'] = [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => $parameters,
                                ],
                            ],
                        ],
                    ];
                }

                // Assign the operation to the correct path and method
                $paths[$uri][$method] = $operation;
            }
            dd($paths);
        }

        return $paths;
    }

    /**
     * @throws ReflectionException
     */
    protected function getRequestParameters(\Illuminate\Routing\Route $route)
    {
        $action = $route->getAction('uses');
        dump(is_callable($action));
        // Check if the action is a controller method
        if (!is_callable($action) && is_string($action)) {
            list($controller, $method) = Str::parseCallback($action);
            $reflectionMethod = new ReflectionMethod($controller, $method);
            dd($reflectionMethod->getParameters());
            $parameters = [];

            foreach ($reflectionMethod->getParameters() as $param) {
                // Check if the parameter is a FormRequest
                $paramType = $param->getType() ? $param->getName() : null;
                if ($paramType && is_subclass_of($paramType, FormRequest::class)) {
                    $requestClass = $paramType->getName();
                    $formRequestReflection = new ReflectionClass($requestClass);
                    if ($formRequestReflection->isInstantiable()) {
                        /** @var FormRequest $requestInstance */
                        $requestInstance = app($requestClass);

                        // Extract and process the validation rules
                        $rules = $requestInstance->rules();
                        $parameters[] = $this->convertRulesToOpenApiParameters($rules);
                    }
                }
            }

            return $parameters;
        }

        return [];
    }

    // Method to convert Laravel validation rules to OpenAPI parameters (simplified example)
    protected function convertRulesToOpenApiParameters(array $rules)
    {
        $parameters = [];

        foreach ($rules as $field => $rule) {
            $parameter = [
                'name' => $field,
                'in' => 'body', // Adjust based on the context (query, path, header, cookie)
                'required' => Str::contains($rule, 'required'),
                // 'description' => '', // Populate this if you have field descriptions available
                'schema' => [
                    'type' => 'string', // This should be dynamic, based on the validation type
                ],
            ];
            // Handle other validation rules and map them to proper OpenAPI specifications
            $parameters[] = $parameter;
        }
        return $parameters;
    }

}
