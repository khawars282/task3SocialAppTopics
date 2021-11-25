<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UsersFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return [
            'name' =>'required|string',
            'email' => 'required|email|unique:users,email|max:50',
            'password' => 'required|string|min:6|max:12'
        ];
    }
    public function failedValidation(Validator $validator)
    {
       throw new HttpResponseException(response()->json([
         'success'   => false,
         'message'   => 'Validation errors',
         'data'      => $validator->errors()
       ]));
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' =>'required|string',
            'email' => 'required|email',
            'password' => 'required|string'  
        ];
    }
    public function messages() //OPTIONAL
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'credentials not valid'
        ];
    }
}
