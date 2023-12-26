<?php

namespace Aaliboyev\Lod\Contracts;

interface OpenApiGenerator
{
    public function getGroupedRoutes();

    public function generatePaths($groupedRoutes);

    public function generateOpenApiSchema($groupedRoutes);
}
