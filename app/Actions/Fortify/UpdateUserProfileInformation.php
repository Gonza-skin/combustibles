<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Valida y actualiza el nombre y correo del usuario con sesión.
     *
     * @param  array<string, mixed>  $input
     */
    public function update($user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('usuario', 'EML_USU')->ignore($user->COD_USU, 'COD_USU'),
            ],
        ])->validateWithBag('updateProfileInformation');

        $user->forceFill([
            'NOM_USU' => $input['name'],
            'EML_USU' => $input['email'],
        ])->save();
    }
}
