<?php

namespace Digit7s\InertiaForm\Fields;

use DateTimeInterface;

class DateTimePicker extends Field
{
    protected string $type = 'datetime';

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->suffixIcon = 'calendar';
        $this->meta['has_date'] = true;
        $this->meta['has_time'] = true;
        $this->meta['native'] = true;
    }

    public function displayFormat(string $format): static
    {
        $this->meta['display_format'] = $format;

        return $this;
    }

    public function format(string $format): static
    {
        $this->meta['format'] = $format;

        return $this;
    }

    public function minDate(string|DateTimeInterface $date): static
    {
        $this->meta['min_date'] = $date instanceof DateTimeInterface ? $date->format('Y-m-d H:i:s') : $date;

        return $this;
    }

    public function maxDate(string|DateTimeInterface $date): static
    {
        $this->meta['max_date'] = $date instanceof DateTimeInterface ? $date->format('Y-m-d H:i:s') : $date;

        return $this;
    }

    public function timezone(string $timezone): static
    {
        $this->meta['timezone'] = $timezone;

        return $this;
    }

    public function native(bool $condition = true): static
    {
        $this->meta['native'] = $condition;

        return $this;
    }

    public function seconds(bool $condition = true): static
    {
        $this->meta['has_seconds'] = $condition;

        return $this;
    }

    public function hoursStep(int $step): static
    {
        $this->meta['hours_step'] = $step;

        return $this;
    }

    public function minutesStep(int $step): static
    {
        $this->meta['minutes_step'] = $step;

        return $this;
    }

    public function secondsStep(int $step): static
    {
        $this->meta['seconds_step'] = $step;

        return $this;
    }

    public function disabledDates(array $dates): static
    {
        $this->meta['disabled_dates'] = $dates;

        return $this;
    }

    public function firstDayOfWeek(int $day): static
    {
        $this->meta['first_day_of_week'] = $day;

        return $this;
    }
}
