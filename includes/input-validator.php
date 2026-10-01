<?php
/**
 * Input Validator — reusable form validation
 * Usage:
 *   $v = new InputValidator($_POST);
 *   $v->required('email')->email('email')->minLength('password', 8);
 *   if ($v->fails()) { ... $v->errors() ... }
 */
class InputValidator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** Field must be present and non-empty */
    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field] = "$label is required.";
        }
        return $this;
    }

    /** Field must be a valid email */
    public function email(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label is not a valid email address.";
        }
        return $this;
    }

    /** Field must match a basic Cameroonian phone format or general numeric */
    public function phone(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field])) {
            $phone = preg_replace('/\s+/', '', $this->data[$field]);
            if (!preg_match('/^\+?[0-9]{8,15}$/', $phone)) {
                $this->errors[$field] = "$label must be a valid phone number.";
            }
        }
        return $this;
    }

    /** Minimum string length */
    public function minLength(string $field, int $min, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && mb_strlen((string)$this->data[$field]) < $min) {
            $this->errors[$field] = "$label must be at least $min characters.";
        }
        return $this;
    }

    /** Maximum string length */
    public function maxLength(string $field, int $max, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && mb_strlen((string)$this->data[$field]) > $max) {
            $this->errors[$field] = "$label must not exceed $max characters.";
        }
        return $this;
    }

    /** Field must be numeric */
    public function numeric(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "$label must be a number.";
        }
        return $this;
    }

    /** Field must be a positive number */
    public function positiveNumber(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field])) {
            if (!is_numeric($this->data[$field]) || (float)$this->data[$field] <= 0) {
                $this->errors[$field] = "$label must be a positive number.";
            }
        }
        return $this;
    }

    /** Field value must be in a given list */
    public function inArray(string $field, array $allowed, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = "$label contains an invalid value.";
        }
        return $this;
    }

    /** Two fields must match (e.g. password confirmation) */
    public function matches(string $field, string $otherField, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (isset($this->data[$field]) && isset($this->data[$otherField])) {
            if ($this->data[$field] !== $this->data[$otherField]) {
                $this->errors[$field] = "$label does not match.";
            }
        }
        return $this;
    }

    /** Custom rule with a callback — callback(value) returns true if valid */
    public function custom(string $field, callable $callback, string $errorMessage): static
    {
        if (isset($this->data[$field])) {
            if (!$callback($this->data[$field])) {
                $this->errors[$field] = $errorMessage;
            }
        }
        return $this;
    }

    /** Returns true if there are validation errors */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /** Returns true if validation passed */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /** Returns all error messages as associative array [field => message] */
    public function errors(): array
    {
        return $this->errors;
    }

    /** Returns first error message found, or empty string */
    public function firstError(): string
    {
        return array_values($this->errors)[0] ?? '';
    }

    /** Get a sanitized value from the input */
    public function get(string $field, mixed $default = ''): mixed
    {
        return isset($this->data[$field]) ? $this->data[$field] : $default;
    }

    /** Get a sanitized, trimmed string value */
    public function str(string $field, string $default = ''): string
    {
        return trim((string) ($this->data[$field] ?? $default));
    }

    /** Get an integer value */
    public function int(string $field, int $default = 0): int
    {
        return (int) ($this->data[$field] ?? $default);
    }

    /** Get a float value */
    public function float(string $field, float $default = 0.0): float
    {
        return (float) ($this->data[$field] ?? $default);
    }
}
