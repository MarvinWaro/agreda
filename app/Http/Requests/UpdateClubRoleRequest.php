<?php

namespace App\Http\Requests;

use App\Models\Club;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClubRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Route is gated by auth + verified + admin + permission:clubs.manage.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $club = $this->route('club');
        $clubId = $club instanceof Club ? $club->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('club_roles', 'name')
                    ->where('club_id', $clubId)
                    ->ignore($this->route('role')),
            ],
            'is_default' => ['boolean'],
        ];
    }
}
