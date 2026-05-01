<?php

namespace Digit7s\InertiaForm\Fields;

use Closure;

class CheckboxList extends Field
{
    protected string $type = 'checkbox-list';

    protected mixed $default = [];

    /**
     * Set the options for the checkbox list.
     * Can be an array or a Closure that returns an array.
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
     * Set descriptions for the options.
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
     * Set the number of columns for the grid layout.
     * Can be an integer or a responsive array like ['default' => 1, 'md' => 2].
     */
    public function columns(int|array $columns): static
    {
        $this->meta['columns'] = $columns;

        return $this;
    }

    /**
     * Enable a "Select All / Deselect All" toggle.
     */
    public function bulkToggleable(bool $condition = true): static
    {
        $this->meta['bulkToggleable'] = $condition;

        return $this;
    }

    /**
     * Enable a search input to filter the options.
     */
    public function searchable(bool $condition = true): static
    {
        $this->meta['searchable'] = $condition;

        return $this;
    }

    /**
     * Set a custom placeholder for the search input.
     */
    public function searchPrompt(string $prompt): static
    {
        $this->meta['searchPrompt'] = $prompt;

        return $this;
    }

    /**
     * Disable specific options based on a callback.
     */
    public function disableOptionWhen(Closure $callback): static
    {
        // This is tricky for server-driven UI as the closure can't be easily serialized.
        // We'll evaluate it here if possible or pass specific disabled keys.
        // For now, let's assume we evaluate against the options keys.
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

    /**
     * Minimum number of items that must be selected.
     */
    public function minItems(int $count): static
    {
        $this->meta['minItems'] = $count;

        return $this;
    }

    /**
     * Maximum number of items that can be selected.
     */
    public function maxItems(int $count): static
    {
        $this->meta['maxItems'] = $count;

        return $this;
    }
}
