<?php

namespace Digit7s\InertiaForm\Fields;

class Textarea extends Field
{
    protected string $type = 'textarea';

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->rows(3);
    }

    /**
     * Set the number of rows for the textarea.
     */
    public function rows(int $rows): static
    {
        $this->meta['rows'] = $rows;

        return $this;
    }

    /**
     * Set the number of columns for the textarea.
     */
    public function cols(int $cols): static
    {
        $this->meta['cols'] = $cols;

        return $this;
    }

    /**
     * Enable or disable autosizing for the textarea.
     */
    public function autosize(bool $condition = true): static
    {
        $this->meta['autosize'] = $condition;

        return $this;
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
}
