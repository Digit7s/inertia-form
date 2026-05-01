<?php

namespace Digit7s\InertiaForm\Fields;

class TextInput extends Field
{
    protected string $type = 'text';

    /**
     * Specify the standard input type (text, password, email, etc.).
     */
    public function type(string $type): static
    {
        $this->meta['input_type'] = $type;

        return $this;
    }

    public function email(): static
    {
        return $this->type('email');
    }

    public function password(): static
    {
        return $this->type('password');
    }

    public function numeric(): static
    {
        return $this->type('number');
    }

    public function tel(): static
    {
        return $this->type('tel');
    }

    public function url(): static
    {
        return $this->type('url');
    }

    public function color(): static
    {
        return $this->type('color');
    }

    /**
     * Set the minimum character length.
     */
    public function minLength(int $length): static
    {
        $this->meta['min_length'] = $length;

        return $this;
    }

    /**
     * Set the maximum character length.
     */
    public function maxLength(int $length): static
    {
        $this->meta['max_length'] = $length;

        return $this;
    }

    /**
     * Set the minimum value for numeric inputs.
     */
    public function minValue(int|float $value): static
    {
        $this->meta['min_value'] = $value;

        return $this;
    }

    /**
     * Set the maximum value for numeric inputs.
     */
    public function maxValue(int|float $value): static
    {
        $this->meta['max_value'] = $value;

        return $this;
    }

    /**
     * Set the step increment for numeric inputs.
     */
    public function step(int|float $step): static
    {
        $this->meta['step'] = $step;

        return $this;
    }

    /**
     * Add a prefix label to the input.
     */
    public function prefix(string $text): static
    {
        $this->meta['prefix'] = $text;

        return $this;
    }

    /**
     * Add a suffix label to the input.
     */
    public function suffix(string $text): static
    {
        $this->meta['suffix'] = $text;

        return $this;
    }

    /**
     * Disable the input.
     */
    public function disabled(bool $disabled = true): static
    {
        $this->meta['disabled'] = $disabled;

        return $this;
    }

    /**
     * Mark the input as read-only.
     */
    public function readOnly(bool $readOnly = true): static
    {
        $this->meta['read_only'] = $readOnly;

        return $this;
    }

    /**
     * Enable autofocus browser feature.
     */
    public function autofocus(bool $autofocus = true): static
    {
        $this->meta['autofocus'] = $autofocus;

        return $this;
    }

    /**
     * Set the browser autocomplete value.
     */
    public function autocomplete(string $value): static
    {
        $this->meta['autocomplete'] = $value;

        return $this;
    }
}
