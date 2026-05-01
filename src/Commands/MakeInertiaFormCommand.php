<?php

namespace Digit7s\InertiaForm\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeInertiaFormCommand extends GeneratorCommand
{
    protected $name = 'make:inertia-form';

    protected $description = 'Create a new InertiaForm class';

    protected $type = 'InertiaForm';

    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/form.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Forms';
    }

    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model that the form targets.'],
        ];
    }
}
