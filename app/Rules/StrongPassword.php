<?php

namespace App\Rules;

use App\Services\PasswordSecurityService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    protected array $context;
    protected PasswordSecurityService $securityService;

    /**
     * @param array{name?: string, email?: string, phone?: string} $context
     */
    public function __construct(array $context = [])
    {
        $this->context = $context;
        $this->securityService = app(PasswordSecurityService::class);
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('পাসওয়ার্ডটি একটি বৈধ টেক্সট স্ট্রিং হতে হবে।');
            return;
        }

        $result = $this->securityService->validate($value, $this->context);

        if (!$result['valid']) {
            foreach ($result['errors'] as $error) {
                $fail($error);
            }
        }
    }
}
