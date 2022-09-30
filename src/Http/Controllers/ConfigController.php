<?php

namespace LaraDumps\LaraDumps\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use LaraDumps\LaraDumps\Actions\{GetConfigFileLink, ListConfigKeys, MakeConfigValidatonRules};
use LaraDumps\LaraDumps\Actions\{UpdateConfigFromForm, UpdateEnv};

final class ConfigController extends Controller
{
    public function index(): View
    {
        abort_if(boolval(app()->environment('production')), 404);

        return view('laradumps::config', [
            'configFile' => GetConfigFileLink::handle(),
            'configKeys' => ListConfigKeys::handle(),
        ]);
    }

    public function store(): RedirectResponse
    {
        $validKeys      = request()->validate(MakeConfigValidatonRules::handle());

        UpdateConfigFromForm::handle($validKeys);

        return redirect(route('laradumps.index'));
    }
}
