<?php

namespace Digit7s\InertiaForm\Fields;

use InvalidArgumentException;

class RichTextEditor extends Field
{
    protected string $type = 'rich-text-editor';

    protected mixed $default = '';

    public function placeholder(string $placeholder): static
    {
        $this->meta['placeholder'] = $placeholder;

        return $this;
    }

    public function toolbar(array|bool $toolbar): static
    {
        $this->meta['toolbar'] = $toolbar;

        return $this;
    }

    public function minHeight(string|int $height): static
    {
        $this->meta['minHeight'] = $height;

        return $this;
    }

    public function maxHeight(string|int $height): static
    {
        $this->meta['maxHeight'] = $height;

        return $this;
    }

    public function output(string $type): static
    {
        if (! in_array($type, ['html', 'json'], true)) {
            throw new InvalidArgumentException('RichTextEditor output must be html or json.');
        }

        $this->meta['output'] = $type;

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
