<?php

namespace Digit7s\InertiaForm\Fields;

class Select extends Field
{
    protected string $type = 'select';

    /**
     * Set the options for the select field.
     * Can be a flat array ['value' => 'label'] or a grouped array ['Group' => ['value' => 'label']].
     */
    public function options(array $options): static
    {
        $this->meta['options'] = $options;

        return $this;
    }

    /**
     * Enable multiple selection.
     */
    public function multiple(bool $multiple = true): static
    {
        $this->meta['multiple'] = $multiple;

        return $this;
    }

    /**
     * Enable searching within the dropdown options.
     */
    public function searchable(bool $searchable = true): static
    {
        $this->meta['searchable'] = $searchable;

        return $this;
    }

    /**
     * Populate options from an Eloquent relationship.
     * This will be resolved automatically in the toArray() method of the InertiaForm.
     */
    public function relationship(string $relationName, string $titleColumn): static
    {
        $this->meta['relationship'] = [
            'name' => $relationName,
            'title' => $titleColumn,
        ];

        return $this;
    }

    /**
     * Disable the select field.
     */
    public function disabled(bool $disabled = true): static
    {
        $this->meta['disabled'] = $disabled;

        return $this;
    }
}
