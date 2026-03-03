<?php

namespace App\Http\Requests\Lobby;

use Illuminate\Foundation\Http\FormRequest;

class LobbyJoinRequest extends FormRequest
{
    public const CODE = 'code';
    public const NAME = 'name';
    
    public function rules(): array
    {
        return [
             self::CODE => [
                'required',
                'string',
                'max:4',
                'exists:lobbies,code',
            ],
            self::NAME => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
