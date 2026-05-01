<?php

namespace Digit7s\InertiaForm\Fields;

class DatePicker extends DateTimePicker
{
    protected string $type = 'date';

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->meta['has_time'] = false;
    }
}
