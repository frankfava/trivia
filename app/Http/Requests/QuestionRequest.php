<?php

namespace App\Http\Requests;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
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
            'difficulty' => $this->difficulty ?? QuestionDifficulty::MEDIUM->value,
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
            'type' => ['required', 'string', 'in:'.implode(',', QuestionType::values())],
            'difficulty' => ['required', 'string', 'in:'.implode(',', QuestionDifficulty::values())],
            'category' => ['required_without:category_id', 'string'],
            'category_id' => ['required_without:category', 'exists:categories,id'],
            'question' => ['required', 'string', 'max:500'],
            'correct_answer' => ['nullable', 'string', 'max:255'],
            'incorrect_answers' => ['nullable', 'array'],
            'incorrect_answers.*' => ['string', 'max:255'],
        ];
    }
}
