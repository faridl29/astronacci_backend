<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = Auth::id();
        
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => "sometimes|required|string|email|max:255|unique:users,email,{$userId}",
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'avatar_base64' => 'nullable|string', // Base64 string validation
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed|different:current_password',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('current_password') && $this->filled('password')) {
                if (!Hash::check($this->current_password, Auth::user()->password)) {
                    $validator->errors()->add('current_password', 'Current password is incorrect.');
                }
            }

            if ($this->filled('avatar_base64')) {
                $base64Data = $this->avatar_base64;
                
                if (strpos($base64Data, 'data:image') === 0) {
                    $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                }

                if (!base64_decode($base64Data, true)) {
                    $validator->errors()->add('avatar_base64', 'Invalid image format.');
                    return;
                }

                $decodedSize = strlen(base64_decode($base64Data));
                $maxSize = 5 * 1024 * 1024; // 5MB
                
                if ($decodedSize > $maxSize) {
                    $validator->errors()->add('avatar_base64', 'Image size is too large. Maximum 5MB allowed.');
                }

                $originalBase64 = $this->avatar_base64;
                if (strpos($originalBase64, 'data:image') === 0) {
                    $mimeType = substr($originalBase64, 5, strpos($originalBase64, ';') - 5);
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    
                    if (!in_array($mimeType, $allowedTypes)) {
                        $validator->errors()->add('avatar_base64', 'Invalid image type. Only JPEG, PNG, GIF, and WebP are allowed.');
                    }
                }
            }
        });
    }
}
