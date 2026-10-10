<?php

namespace Digit7s\InertiaForm\Commands;

use Illuminate\Console\GeneratorCommand;

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
}
