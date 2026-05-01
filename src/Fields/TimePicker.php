<?php

namespace Digit7s\InertiaForm\Fields;

class TimePicker extends DateTimePicker
{
    protected string $type = 'time';

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->suffixIcon = 'clock';
        $this->meta['has_date'] = false;
    }
}
