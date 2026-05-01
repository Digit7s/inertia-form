<?php
 
namespace Digit7s\InertiaForm\Layouts;
 
class Grid
{
    protected int $columns = 2;
 
    protected array $schema = [];

    protected int|string $columnSpan = 'full';
 
    public function __construct(int $columns = 2)
    {
        $this->columns = $columns;
    }
 
    public static function make(int $columns = 2): static
    {
        return new static($columns);
    }
 
    public function schema(array $schema): static
    {
        $this->schema = $schema;
 
        return $this;
    }
 
    public function getSchema(): array
    {
        return $this->schema;
    }

    public function columnSpan(int|string $span): static
    {
        $this->columnSpan = $span;

        return $this;
    }

    public function getColumns(): int
    {
        return $this->columns;
    }
 
    public function toArray(): array
    {
        return [
            'type' => 'grid',
            'columns' => $this->columns,
            'column_span' => $this->columnSpan,
            'schema' => collect($this->schema)->map(fn ($item) => $item->toArray())->toArray(),
        ];
    }
}
