<?php

use Digit7s\InertiaForm\Fields\TimePicker;

it('sets the correct type and default metadata', function () {
    $field = TimePicker::make('start_time');

    $data = $field->toArray();

    expect($data)
        ->type->toBe('time')
        ->suffix_icon->toBe('clock');

    expect($data['meta'])
        ->has_date->toBeFalse()
        ->has_time->toBeTrue(); // Inherited from DateTimePicker
});

it('can set time steps', function () {
    $field = TimePicker::make('start_time')
        ->hoursStep(2)
        ->minutesStep(15)
        ->secondsStep(30);

    $data = $field->toArray();

    expect($data['meta'])
        ->hours_step->toBe(2)
        ->minutes_step->toBe(15)
        ->seconds_step->toBe(30);
});
