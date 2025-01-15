<?php

namespace App\Http\Requests\Api\V1;

use App\Permissions\V1\Abilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

// /**
//  * 
//  * @bodyParam data.attributes.title string required The ticket's title. No-example
//  * @bodyParam data.relationships.author.data.id integer required The author id. No-example
//  */
class StoreTicketRequest extends BaseTicketRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $isTicketController = $this->routeIs('tickets.store');
        $authorIdAttr = $isTicketController ? 'data.relationships.author.data.id' : 'author';
        $user = Auth::user();
        $authorRule = 'integer|required|exists:users,id';

        $rules = [
            'data' => 'required|array',
            'data.attributes' => 'required|array',
            'data.attributes.title' => 'required|string',
            'data.attributes.description' => 'required|string',
            'data.attributes.status' => 'required|string|in:A,C,H,X',
        ];

        if ($isTicketController) {
            $rules['data.relationships'] = 'required|array';
            $rules['data.relationships.author'] = 'required|array';
            $rules['data.relationships.author.data'] = 'required|array';
        }

        $rules[$authorIdAttr] = $authorRule . '|size:' . $user->id;


        if ($user->tokenCan(Abilities::CreateTicket)) {
            $rules[$authorIdAttr] = $authorRule;
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        if ($this->routeIs('authors.tickets.store')) {
            $this->merge([
                'author' => $this->route('author')
            ]);
        }
    }

    public function bodyParameters()
    {
        $documentation = [
            'data.attributes.title' => [
                'description' => 'The ticket\'s title (method)',
                'example' => 'No-example'
            ],
            'data.attributes.description' => [
                'description' => 'The ticket\'s description',
                'example' => 'No-example'
            ],
            'data.attributes.status' => [
                'description' => 'The ticket\'s status',
                'example' => 'A, C, H, or X'
            ],
        ];

        if ($this->routeIs('tickets.store')) {
            $documentation['data.relationships.author.data.id'] = [
                'description' => 'The author id',
                'example' => 'No-example'
            ];
        } else {
            $documentation['author'] = [
                'description' => 'The author id',
                'example' => 'No-example'
            ];
        }

        return $documentation;
    }
}
