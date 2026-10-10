<?php

namespace Digit7s\InertiaForm\Fields;

class MarkdownEditor extends Field
{
    protected string $type = 'markdown-editor';

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

    public function preview(bool $condition = true): static
    {
        $this->meta['preview'] = $condition;

        return $this;
    }

    public function sideBySide(bool $condition = true): static
    {
        $this->meta['sideBySide'] = $condition;

        return $this;
    }

    public function spellChecker(bool $condition = true): static
    {
        $this->meta['spellChecker'] = $condition;

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
