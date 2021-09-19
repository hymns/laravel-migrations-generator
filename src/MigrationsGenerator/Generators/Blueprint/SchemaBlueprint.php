<?php

namespace MigrationsGenerator\Generators\Blueprint;

use Illuminate\Support\Facades\Config;
use MigrationsGenerator\Generators\MigrationConstants\Method\SchemaBuilder;
use MigrationsGenerator\Generators\TableNameGenerator;
use MigrationsGenerator\Generators\Writer\WriterConstant;

class SchemaBlueprint
{
    use Stringable;

    /** @var string */
    private $table;

    /** @var string */
    private $connection;

    /** @var string */
    private $schemaBuilder;

    /** @var TableBlueprint|null */
    private $blueprint;

    /**
     * SchemaBlueprint constructor.
     * @param  string  $table
     * @param  string  $connection
     * @param  string  $schemaBuilder
     * @see SchemaBuilder for $builderType
     */
    public function __construct(string $connection, string $table, string $schemaBuilder)
    {
        $this->table         = $table;
        $this->connection    = $connection;
        $this->schemaBuilder = $schemaBuilder;
        $this->blueprint     = null;
    }

    public function setBlueprint(TableBlueprint $blueprint): void
    {
        $this->blueprint = $blueprint;
    }

    public function toString(): string
    {
        if ($this->connection !== Config::get('database.default')) {
            $schema = "Schema::".SchemaBuilder::CONNECTION."('$this->connection')->$this->schemaBuilder";
        } else {
            $schema = "Schema::$this->schemaBuilder";
        }

        $tableWithoutPrefix = app(TableNameGenerator::class)->stripPrefix($this->table);

        $lines = [];
        if ($this->blueprint !== null) {
            $lines[] = "$schema('$tableWithoutPrefix', function (Blueprint \$table) {";
            // Add 1 tabulation to indent blueprint definition.
            $lines[] = WriterConstant::TAB.$this->blueprint->toString();
            $lines[] = "});";
        } else {
            $lines[] = "$schema('$tableWithoutPrefix');";
        }

        return $this->implodeLines($lines, 2);
    }
}
