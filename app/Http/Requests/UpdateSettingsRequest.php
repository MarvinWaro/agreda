<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Route is already gated by auth + verified + admin middleware.
        return true;
    }

    /**
     * Accept either the bare "Embed a map" URL or the full <iframe> snippet
     * Google Maps offers, then normalise it down to the embeddable src URL.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('map_embed_url')) {
            return;
        }

        $value = trim((string) $this->input('map_embed_url'));

        if (str_contains($value, '<iframe') && preg_match('/src=["\']([^"\']+)["\']/i', $value, $matches) === 1) {
            $value = trim($matches[1]);
        }

        $this->merge(['map_embed_url' => $value]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'map_embed_url' => ['nullable', 'string', 'max:2000', $this->embeddableMapRule()],
            'address' => ['nullable', 'string', 'max:255'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Only Google's framed map URLs render inside an iframe; share links
     * (maps.app.goo.gl, /maps/place/…) send X-Frame-Options and fail.
     */
    private function embeddableMapRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $url = (string) $value;

            if ($url === '') {
                return;
            }

            $isEmbeddable = str_starts_with($url, 'https://www.google.com/maps/embed')
                || (str_contains($url, 'google.com/maps') && str_contains($url, 'output=embed'));

            if (! $isEmbeddable) {
                $fail('Use the "Embed a map" link from Google Maps (Share → Embed a map). It starts with https://www.google.com/maps/embed — a normal share link cannot be embedded.');
            }
        };
    }
}
