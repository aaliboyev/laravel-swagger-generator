<?php

namespace Aaliboyev\Lod\Http\Controllers;

use Aaliboyev\Lod\Services\SchemaGenerator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Aaliboyev\Lod\Contracts\OpenApiGenerator;


class OpenApiController extends BaseController
{
    protected OpenApiGenerator $openApiGenerator;

    public function __construct(OpenApiGenerator $openApiGenerator)
    {
        $this->openApiGenerator = $openApiGenerator;
    }

    /**
     * Create a new user.
     *
     * @param  \App\Http\Requests\UserStoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *   path="/api/users",
     *   summary="Create a new user",
     *   description="Register a new user providing necessary information",
     *   operationId="users.store",
     *   tags={"Users"},
     *   @OA\RequestBody(
     *       required=true,
     *       description="Pass user credentials",
     *       @OA\JsonContent(
     *           required={"name","email","password"},
     *           @OA\Property(property="name", type="string", format="string", example="John Doe"),
     *           @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *           @OA\Property(property="password", type="string", format="password", example="Passw0rd!"),
     *           @OA\Property(property="password_confirmation", type="string", format="password", example="Passw0rd!")
     *       ),
     *   ),
     *   @OA\Response(
     *       response=201,
     *       description="User created successfully",
     *       @OA\JsonContent(
     *           @OA\Property(property="message", type="string", example="User created successfully."),
     *           @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *       )
     *   ),
     *   @OA\Response(
     *       response=400,
     *       description="Bad request"
     *   )
     * )
     */

    public function index(Request $request)
    {
        // Retrieve and group all API routes
        $groupedRoutes = $this->openApiGenerator->getGroupedRoutes();

        $paths = $this->openApiGenerator->generatePaths($groupedRoutes);

        // Generate OpenAPI schema JSON
        $openapi = $this->openApiGenerator->generateOpenApiSchema($groupedRoutes);

        // Return the JSON response
        return response()->json($openapi);
    }
}
