<?php

namespace Digit7s\InertiaForm;

use Digit7s\InertiaForm\Fields\Field;
use Digit7s\InertiaForm\Layouts\Grid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class InertiaForm
{
    /** @var mixed The model or data resource for the form. */
    protected mixed $resource = null;

    /** @var string|null The explicit form action URL. */
    protected ?string $action = null;

    /** @var string|null The explicit form method. */
    protected ?string $method = null;

    /** @var string The layout style of the form. */
    protected string $style = 'default';

    /** @var string|null The title of the form section. */
    protected ?string $title = null;

    /** @var string|null The description of the form section. */
    protected ?string $description = null;

    /** @var string|\Closure|null The redirect URL after a successful submission. */
    protected string|\Closure|null $redirectUrl = null;

    public function __construct(mixed $resource = null)
    {
        $this->resource = $resource;
    }

    /**
     * Create a new form instance.
     */
    public static function make(mixed $resource = null): static
    {
        return new static($resource);
    }

    /**
     * Set the form action URL.
     */
    public function action(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Set the form method (post, put, patch, or delete).
     */
    public function method(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Set the layout style of the form.
     */
    public function style(string $style): static
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Set the title of the form section.
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set the description of the form section.
     */
    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the redirect URL after a successful submission.
     */
    public function redirect(string|\Closure|null $url): static
    {
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * Resolve and get the redirect URL.
     */
    public function getRedirectUrl(): ?string
    {
        $this->schema();

        if ($this->redirectUrl instanceof \Closure) {
            return ($this->redirectUrl)($this->resource);
        }

        return $this->redirectUrl;
    }

    /**
     * Define the form schema.
     *
     * @return array<int, Field>
     */
    abstract public function schema(): array;

    /**
     * Transform the form into an array for Inertia.
     */
    public function toArray(): array
    {
        $schema = $this->resolveSchema($this->schema());
        $defaults = $this->resolveDefaults($this->schema());

        // Auto-detect method if not explicitly set.
        $method = $this->method;
        if (! $method && $this->resource instanceof Model) {
            $method = $this->resource->exists ? 'put' : 'post';
        }

        return [
            'schema' => $schema,
            'defaults' => $defaults,
            'action' => $this->action,
            'method' => $method ?? 'post',
            'style' => $this->style,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }

    protected function resolveSchema(array $schema): array
    {
        return collect($schema)->map(function ($item) {
            if ($item instanceof Grid) {
                $data = $item->toArray();
                $data['schema'] = $this->resolveSchema($item->getSchema());

                return $data;
            }

            if ($item instanceof Field) {
                $data = $item->toArray();

                // Resolve relationships automatically for Select fields.
                if (isset($data['meta']['relationship'])) {
                    $relationData = $data['meta']['relationship'];

                    try {
                        if ($this->resource instanceof Model) {
                            $relation = $this->resource->{$relationData['name']}();
                            $relatedModel = $relation->getRelated();
                            $keyName = $relatedModel->getKeyName();

                            $data['meta']['options'] = $relatedModel::query()
                                ->pluck($relationData['title'], $keyName)
                                ->toArray();
                        }
                    } catch (\Throwable $e) {
                        // Fail silently
                    }
                }

                return $data;
            }

            return $item;
        })->toArray();
    }

    protected function resolveDefaults(array $schema): array
    {
        $defaults = [];

        foreach ($schema as $item) {
            if ($item instanceof Grid) {
                $defaults = array_merge($defaults, $this->resolveDefaults($item->getSchema()));

                continue;
            }

            if ($item instanceof Field) {
                $data = $item->toArray();
                $value = $data['default'];

                // Attempt to resolve the value from the resource if available.
                if ($this->resource && $data['name'] !== 'password') {
                    try {
                        $resolvedValue = $this->resource->{$data['name']};

                        if ($resolvedValue instanceof Collection) {
                            if ($data['name'] === 'roles') {
                                $value = $resolvedValue->pluck('name')->toArray();
                            } else {
                                $value = $resolvedValue->modelKeys();
                            }
                        } elseif ($resolvedValue instanceof \DateTimeInterface) {
                            if ($data['type'] === 'time') {
                                $value = $resolvedValue->format('H:i');
                            } elseif ($data['type'] === 'date') {
                                $value = $resolvedValue->format('Y-m-d');
                            } else {
                                $value = $resolvedValue->format('Y-m-d H:i:s');
                            }
                        } elseif ($resolvedValue !== null) {
                            $value = $resolvedValue;
                        }
                    } catch (\Throwable $e) {
                        // Ignore
                    }
                }

                $defaults[$data['name']] = $value;
            }
        }

        return $defaults;
    }
}
