<?php

namespace Digit7s\InertiaForm\Fields;

use Closure;
use InvalidArgumentException;

class Select extends Field
{
    protected string $type = 'select';

    protected ?Closure $relationshipOptionsResolver = null;

    protected int $relationshipOptionsLimit = 100;

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
     * Set an explicit constrained query for relationship options.
     *
     * The callback receives the Eloquent builder from the configured relation,
     * including its relation constraints and model scopes. Return that builder
     * (or another Eloquent builder) after applying the host application's
     * tenant and authorization filters. Return the same builder instance so
     * the configured relation constraints cannot be replaced accidentally.
     */
    public function relationshipOptions(Closure $constrain, int $limit = 100): static
    {
        if ($limit < 1 || $limit > 500) {
            throw new InvalidArgumentException('Relationship option limits must be between 1 and 500.');
        }

        $this->relationshipOptionsResolver = $constrain;
        $this->relationshipOptionsLimit = $limit;

        return $this;
    }

    public function hasRelationshipOptionsResolver(): bool
    {
        return $this->relationshipOptionsResolver !== null;
    }

    public function getRelationshipOptionsResolver(): ?Closure
    {
        return $this->relationshipOptionsResolver;
    }

    public function getRelationshipOptionsLimit(): int
    {
        return $this->relationshipOptionsLimit;
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
