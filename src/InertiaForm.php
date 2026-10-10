<?php

namespace Digit7s\InertiaForm;

use Digit7s\InertiaForm\Fields\Field;
use Digit7s\InertiaForm\Fields\Select;
use Digit7s\InertiaForm\Layouts\Grid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use LogicException;
use RuntimeException;
use Throwable;

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
     * @return array<int, Field|Grid>
     */
    abstract public function schema(): array;

    /**
     * Transform the form into an array for Inertia.
     */
    public function toArray(): array
    {
        $fields = $this->schema();
        $schema = $this->resolveSchema($fields);
        $defaults = $this->resolveDefaults($fields);

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

                // Explicit options take precedence, including an intentionally empty list.
                if ($item instanceof Select && isset($data['meta']['relationship']) && ! array_key_exists('options', $data['meta'])) {
                    $relationship = $data['meta']['relationship'];
                    $fieldName = $data['name'];

                    if (! $item->hasRelationshipOptionsResolver()) {
                        throw new LogicException("Select field '{$fieldName}' uses relationship '{$relationship['name']}' without an explicit constrained relationshipOptions() query.");
                    }

                    if (! $this->resource instanceof Model) {
                        throw new LogicException("Select field '{$fieldName}' requires an Eloquent model resource to resolve relationship options.");
                    }

                    try {
                        $relation = $this->resource->{$relationship['name']}();
                        if (! $relation instanceof Relation) {
                            throw new LogicException('Configured method is not an Eloquent relationship.');
                        }

                        $relatedModel = $relation->getRelated();
                        $resolver = $item->getRelationshipOptionsResolver();
                        $query = $relation->getQuery();
                        $constrainedQuery = $resolver($query);

                        if ($constrainedQuery !== $query) {
                            throw new LogicException('Relationship option callbacks must constrain and return the supplied query builder.');
                        }

                        $maximumRows = $item->getRelationshipOptionsLimit() + 1;
                        $existingLimit = $query->getQuery()->limit;
                        $query->limit($existingLimit === null ? $maximumRows : min($existingLimit, $maximumRows));
                        $options = $query->pluck($relationship['title'], $relatedModel->qualifyColumn($relatedModel->getKeyName()));
                    } catch (Throwable $exception) {
                        // Keep database details and bindings out of user-facing exceptions.
                        throw new RuntimeException("Unable to resolve options for select field '{$fieldName}' from relationship '{$relationship['name']}'. Check the relationship name, query constraints, and title column.");
                    }

                    if ($options->count() > $item->getRelationshipOptionsLimit()) {
                        throw new LogicException("Select field '{$fieldName}' relationship options exceed the configured limit of {$item->getRelationshipOptionsLimit()}. Add more restrictive query constraints or raise the limit up to 500.");
                    }

                    $data['meta']['options'] = $options->all();
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

                if ($this->resource !== null && (! $this->isSensitiveField($data) || $item->allowsSensitiveDefaultHydration())) {
                    $resolved = $this->resourceValue($item, $data);
                    if ($resolved['found']) {
                        $resolvedValue = $resolved['value'];

                        if ($resolvedValue instanceof Collection) {
                            if ($data['name'] === 'roles') {
                                $value = $resolvedValue->pluck('name')->all();
                            } else {
                                $value = $resolvedValue->map(function ($related) use ($data) {
                                    if ($related instanceof Model) {
                                        return $related->getKey();
                                    }

                                    if ($related instanceof \BackedEnum) {
                                        return $related->value;
                                    }

                                    if (is_scalar($related) || $related === null) {
                                        return $related;
                                    }

                                    throw new RuntimeException("Collection default for field '{$data['name']}' contains an unsupported value.");
                                })->all();
                            }
                        } elseif ($resolvedValue instanceof \DateTimeInterface) {
                            if ($data['type'] === 'time') {
                                $value = $resolvedValue->format('H:i');
                            } elseif ($data['type'] === 'date') {
                                $value = $resolvedValue->format('Y-m-d');
                            } else {
                                $value = $resolvedValue->format('Y-m-d H:i:s');
                            }
                        } elseif ($resolvedValue instanceof \BackedEnum) {
                            $value = $resolvedValue->value;
                        } elseif ($resolvedValue instanceof \Stringable) {
                            $value = (string) $resolvedValue;
                        } elseif (is_scalar($resolvedValue) || is_array($resolvedValue) || $resolvedValue === null) {
                            $value = $resolvedValue;
                        } else {
                            throw new RuntimeException("Default for field '{$data['name']}' has an unsupported value type.");
                        }
                    }
                }

                $defaults[$data['name']] = $value;
            }
        }

        return $defaults;
    }

    /** @return array{found: bool, value: mixed} */
    protected function resourceValue(Field $field, array $data): array
    {
        $name = $data['name'];

        try {
            if ($this->resource instanceof Model) {
                // Only resolve declared model attributes/accessors, or an explicitly
                // configured relationship. Never probe arbitrary model properties.
                if ($this->resource->hasAttribute($name)) {
                    return ['found' => true, 'value' => $this->resource->getAttribute($name)];
                }

                $relationshipName = $data['meta']['relationship']['name'] ?? null;
                if ($field instanceof Select && $field->hasRelationshipOptionsResolver() && $relationshipName === $name && method_exists($this->resource, $relationshipName)) {
                    $relationship = $this->resource->{$relationshipName}();
                    if ($relationship instanceof Relation) {
                        $relatedModel = $relationship->getRelated();
                        $key = $relatedModel->qualifyColumn($relatedModel->getKeyName());
                        $query = $relationship->getQuery();
                        $constrainedQuery = ($field->getRelationshipOptionsResolver())($query);

                        if ($constrainedQuery !== $query) {
                            throw new RuntimeException("Relationship default query for field '{$name}' must return its supplied builder.");
                        }

                        $maximumRows = $field->getRelationshipOptionsLimit() + 1;
                        $existingLimit = $query->getQuery()->limit;
                        $query->limit($existingLimit === null ? $maximumRows : min($existingLimit, $maximumRows));
                        $values = $query->pluck($key);

                        if ($values->count() > $field->getRelationshipOptionsLimit()) {
                            throw new RuntimeException("Relationship defaults for field '{$name}' exceed the configured option limit.");
                        }

                        return ['found' => true, 'value' => $values];
                    }
                }

                return ['found' => false, 'value' => null];
            }

            if (is_array($this->resource)) {
                return array_key_exists($name, $this->resource)
                    ? ['found' => true, 'value' => $this->resource[$name]]
                    : ['found' => false, 'value' => null];
            }

            if (is_object($this->resource) && property_exists($this->resource, $name)) {
                return ['found' => true, 'value' => $this->resource->{$name}];
            }
        } catch (Throwable) {
            // Expose field-level context while keeping accessor and database details private.
            throw new RuntimeException("Unable to resolve the default for field '{$name}'. Check the configured resource attribute or accessor.");
        }

        return ['found' => false, 'value' => null];
    }

    protected function isSensitiveField(array $data): bool
    {
        $name = strtolower((string) $data['name']);
        $normalizedName = preg_replace('/[^a-z0-9]/', '', $name) ?? $name;
        $inputType = strtolower((string) ($data['meta']['input_type'] ?? ''));

        if ($inputType === 'password') {
            return true;
        }

        foreach ([
            'password', 'passwordconfirmation', 'currentpassword', 'newpassword',
            'token', 'apitoken', 'accesstoken', 'refreshtoken', 'remembertoken',
            'secret', 'clientsecret', 'apikey', 'appkey', 'encryptionkey', 'privatekey', 'signingkey',
            'authorization', 'credentials', 'sessionid', 'twofactorsecret', 'recoverycodes',
        ] as $sensitiveName) {
            if ($normalizedName === $sensitiveName || str_contains($normalizedName, $sensitiveName)) {
                return true;
            }
        }

        return (bool) ($data['meta']['sensitive'] ?? false);
    }
}
