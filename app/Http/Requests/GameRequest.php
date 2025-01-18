<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'number_of_questions' => $this->number_of_questions ?? 20,
            'max_players' => $this->max_players ?? 5,
            'show_correct_answers' => $this->show_correct_answers ?? false,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => [
                'sometimes',
                'required_without:id',
                'string',
                'max:255',
            ],
            'number_of_questions' => [
                'sometimes',
                'integer',
                'nullable',
                'min:1',
                'max:50',
            ],
            'max_players' => [
                'sometimes',
                'integer',
                'nullable',
                'min:1',
                'max:50',
            ],
            'show_correct_answers' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
