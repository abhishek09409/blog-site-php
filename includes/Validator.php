<?php

declare(strict_types=1);

class Validator
{
    private array $errors = [];

    public function __construct(private array $data, private array $rules)
    {
    }

    public static function make(array $data, array $rules): self
    {
        $validator = new self($data, $rules);
        $validator->validate();
        return $validator;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }
            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $message = $this->check($field, $value, $name, $param);
                if ($message !== null) {
                    $this->errors[$field][] = $message;
                    break;
                }
            }
        }
    }

    private function check(string $field, mixed $value, string $name, ?string $param): ?string
    {
        $label = ucfirst(str_replace('_', ' ', $field));
        $empty = $value === null || $value === '';
        if ($name === 'required' && $empty) {
            return $label . ' is required.';
        }
        if ($empty && $name !== 'required') {
            return null;
        }
        $string = is_scalar($value) ? (string) $value : '';
        return match ($name) {
            'email' => filter_var($string, FILTER_VALIDATE_EMAIL) ? null : 'Enter a valid email address.',
            'min' => mb_strlen($string) >= (int) $param ? null : $label . ' must be at least ' . (int) $param . ' characters.',
            'max' => mb_strlen($string) <= (int) $param ? null : $label . ' must be ' . (int) $param . ' characters or fewer.',
            'username' => preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $string) ? null : 'Use lowercase letters, numbers, and hyphens.',
            'slug' => preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $string) ? null : 'Use a lowercase slug with hyphens.',
            'hex' => preg_match('/^#[0-9A-Fa-f]{6}$/', $string) ? null : 'Enter a hex color such as #F05A2A.',
            'url' => safe_url($string) ? null : 'Enter a valid http or https URL.',
            'int' => filter_var($string, FILTER_VALIDATE_INT) !== false ? null : $label . ' must be a number.',
            'in' => in_array($string, explode(',', (string) $param), true) ? null : $label . ' is not valid.',
            'confirmed' => $string === (string) ($this->data[$field . '_confirmation'] ?? '') ? null : $label . ' confirmation does not match.',
            'password' => (preg_match('/[A-Za-z]/', $string) && preg_match('/\d/', $string)) ? null : 'Use at least one letter and one number.',
            default => null,
        };
    }
}
