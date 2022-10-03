<?php

namespace LaraDumps\LaraDumps\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use LaraDumps\LaraDumps\Actions\UpdateConfigFromForm;
use LaraDumps\LaraDumps\Actions\{GetConfigFileLink, ListConfigKeys, MakeConfigValidatonRules};

final class ConfigController extends Controller
{
    public function index(): Application|Factory|\Illuminate\Contracts\View\View
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

        return redirect(route('laradumps.index'))->with('success', 'Configuration updated successfully!');
    }
}
