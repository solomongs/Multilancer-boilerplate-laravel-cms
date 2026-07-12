<?php

it('exposes the Filament admin panel only from soloadmin', function () {
    expect(route('filament.admin.auth.login', absolute: false))
        ->toBe('/soloadmin/login');
});
