<?php

namespace Aaliboyev\Lod\Services;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ReflectionClass;

class SchemaGenerator
{
    /**
     * Generate a schema array based on a Laravel Eloquent model.
     *
     * @param Model $modelInstance An instance of the Eloquent model
     * @return array
     */
    public static function generateFromModel(Model $modelInstance): array
    {
        $schema = [];

        // Retrieve model fillable attributes
        $attributes = $modelInstance->getFillable();

        // Get table name for direct database introspection
        $tableName = $modelInstance->getTable();

        // Retrieve column information from the database
        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);

        // If you prefer to rely on the $casts property:
        $casts = $modelInstance->getCasts();

        foreach ($attributes as $attribute) {
            if (in_array($attribute, $columns)) {
                $castType = $casts[$attribute] ?? 'string'; // Default to string if cast is not defined
                $type = self::getOpenApiType($castType);

                $schema[$attribute] = [
                    'type' => $type,
                    // Add formats, example values, and descriptions as needed
                ];
            }
        }

        return $schema;
    }

    /**
     * Map Laravel cast types to OpenAPI data types.
     *
     * @param string $castType The Laravel cast type
     * @return string
     */
    private static function getOpenApiType(string $castType): string
    {
        return match ($castType) {
            'int', 'integer', 'increment' => 'integer',
            'real', 'float', 'double' => 'number',
            'bool', 'boolean' => 'boolean',
            'json' => 'object',
            'string', 'char', 'text' => 'string',
            'date', 'datetime', 'timestamp' => 'string',
            default => 'string',
        };
    }

    public static function generateDtoClass($attrs, $className): string
    {
        $schemaArray = $attrs;
        $propertiesStr = self::generateProperties($schemaArray);

        // Template for the DTO class
        return <<<CLASS
        <?php

        namespace App\DTO;

        class $className
        {
        $propertiesStr
        }

        CLASS;
    }

    /**
     * Create the property definitions for the DTO from the schema array.
     *
     * @param array $schemaArray The schema array
     * @return string The properties as PHP code
     */
    private static function generateProperties(array $schemaArray): string
    {
        $properties = [];

        foreach ($schemaArray as $attribute => $details) {
            $type = $details['type'] ?? 'string'; // Assume string type if not specified
            // Could also include other details such as 'example' or 'description' as comments
            // or may use additional custom annotations as needed (e.g., for validation)
            $phpType = match ($type) {
                'integer' => 'int',
                'number' => 'float',
                'boolean' => 'bool',
                default => $type,
            };
            $properties[] = <<<PROP
                /**
                 * @var $type
                 */
                public $phpType \$$attribute;

            PROP;
        }

        return implode("\n", $properties);
    }

    public static function writeDtoToFile(string $dtoClass, string $className, string $path = '/app/DTO'): void
    {
// Sanitize class name to prevent invalid file naming
        $className = preg_replace('/[^A-Za-z0-9_]/', '', $className);

        // Normalize the directory path to use the correct DIRECTORY_SEPARATOR
        $path = rtrim(base_path() . $path, '/\\') . DIRECTORY_SEPARATOR . $className . '.php';

        // Convert file path to use the correct DIRECTORY_SEPARATOR for the current OS
        $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        // Check if the directory exists and create it if not
        $directoryPath = dirname($filePath);
        if (!file_exists($directoryPath)) {
            if (!mkdir($directoryPath, 0777, true) && !is_dir($directoryPath)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directoryPath));
            }
        }

        // Attempt to write the DTO class code to the file
        if (false === file_put_contents($filePath, $dtoClass)) {
            throw new \RuntimeException(sprintf('Failed to write DTO class to file at "%s"', $filePath));
        }
    }
}
