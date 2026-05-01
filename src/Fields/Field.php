<?php

namespace Digit7s\InertiaForm\Fields;

use Illuminate\Support\Str;

abstract class Field
{
    /** @var string The dynamic Vue component type (e.g. text, select) */
    protected string $type;

    protected string $label;

    protected mixed $default = null;

    protected bool $required = false;

    protected array $meta = [];

    protected ?string $placeholder = null;

    protected ?string $suffixIcon = null;

    protected int|string|null $columnSpan = null;

    public function __construct(protected string $name)
    {
        $this->label = Str::headline($name);
    }

    /**
     * Named constructor for the field.
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * Set the field label.
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the default value.
     */
    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    /**
     * Mark the field as required (frontend hint).
     */
    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Set the field placeholder.
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Set the suffix icon.
     */
    public function suffixIcon(string $icon): static
    {
        $this->suffixIcon = $icon;

        return $this;
    }

    /**
     * Set the column span.
     */
    public function columnSpan(int|string $span): static
    {
        $this->columnSpan = $span;

        return $this;
    }

    /**
     * Add any extra metadata for the frontend.
     */
    public function meta(string $key, mixed $value): static
    {
        $this->meta[$key] = $value;

        return $this;
    }

    /**
     * Serialize the field definition for the frontend.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'placeholder' => $this->placeholder,
            'type' => $this->type,
            'default' => $this->default,
            'required' => $this->required,
            'suffix_icon' => $this->suffixIcon,
            'column_span' => $this->columnSpan,
            'meta' => $this->meta,
        ];
    }
}
