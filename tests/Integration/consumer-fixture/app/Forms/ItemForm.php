<?php

namespace App\Forms;

use Digit7s\InertiaForm\Fields\CheckboxList;
use Digit7s\InertiaForm\Fields\DatePicker;
use Digit7s\InertiaForm\Fields\DateTimePicker;
use Digit7s\InertiaForm\Fields\File;
use Digit7s\InertiaForm\Fields\Radio;
use Digit7s\InertiaForm\Fields\Select;
use Digit7s\InertiaForm\Fields\Textarea;
use Digit7s\InertiaForm\Fields\TextInput;
use Digit7s\InertiaForm\Fields\TimePicker;
use Digit7s\InertiaForm\InertiaForm;
use Digit7s\InertiaForm\Layouts\Grid;
use Illuminate\Database\Eloquent\Model;

class ItemForm extends InertiaForm
{
    public function schema(): array
    {
        return [
            TextInput::make('title')->required(),
            TextInput::make('details.reference')
                ->default(data_get($this->resource, 'details.reference', ''))
                ->required(),
            Textarea::make('notes'),
            Select::make('category')->options(['general' => 'General', 'work' => 'Work', 'personal' => 'Personal'])->required(),
            CheckboxList::make('tags')->options(['urgent' => 'Urgent', 'follow-up' => 'Follow up']),
            Radio::make('priority')->options(['normal' => 'Normal', 'high' => 'High'])->default('normal')->required(),
            Grid::make(2)->schema([
                DatePicker::make('start_date')->required(),
                TimePicker::make('start_time')->required(),
            ]),
            DateTimePicker::make('appointment_at')->required(),
            TextInput::make('password')
                ->password()
                ->default('')
                ->required(! ($this->resource instanceof Model && $this->resource->exists)),
            File::make('attachment'),
        ];
    }
}
