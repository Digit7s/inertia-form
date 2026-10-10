<?php

namespace Digit7s\InertiaForm\Fields;

use Closure;

class Radio extends Field
{
    protected string $type = 'radio';

    protected mixed $default = null;

    /**
     * Set the options for the radio group.
     */
    public function options(array|Closure $options): static
    {
        if ($options instanceof Closure) {
            $options = $options();
        }

        $this->meta['options'] = $options;

        return $this;
    }

    /**
     * Set descriptions for radio options.
     */
    public function descriptions(array|Closure $descriptions): static
    {
        if ($descriptions instanceof Closure) {
            $descriptions = $descriptions();
        }

        $this->meta['descriptions'] = $descriptions;

        return $this;
    }

    /**
     * Display radio options inline.
     */
    public function inline(bool $condition = true): static
    {
        $this->meta['inline'] = $condition;

        return $this;
    }

    /**
     * Set the number of columns.
     */
    public function columns(int|array $columns): static
    {
        $this->meta['columns'] = $columns;

        return $this;
    }

    /**
     * Disable specific options based on callback.
     */
    public function disableOptionWhen(Closure $callback): static
    {
        $options = $this->meta['options'] ?? [];
        $disabledKeys = [];

        foreach (array_keys($options) as $key) {
            if ($callback($key)) {
                $disabledKeys[] = (string) $key;
            }
        }

        $this->meta['disabledOptions'] = $disabledKeys;

        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->meta['disabled'] = $disabled;

        return $this;
    }

    public function readOnly(bool $readOnly = true): static
    {
        $this->meta['read_only'] = $readOnly;

        return $this;
    }
}
