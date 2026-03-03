<?php

namespace App\Http\Requests\Lobby;

use Illuminate\Foundation\Http\FormRequest;

class LobbyCreateRequest extends FormRequest
{
    public const NAME = 'name';
    
    public function rules(): array
    {
        return [
            self::NAME => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
