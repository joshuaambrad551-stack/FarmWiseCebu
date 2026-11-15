<?php

class Validator {
    public static function clean(array $fields, array $input, Lookup $lookup, bool $isCreate): array {
        $clean = [];
        $errors = [];

        foreach ($fields as $name => $f) {
            $label = $f['label'];
            $type = $f['type'];

            if ($type === 'checkbox') {
                $clean[$name] = isset($input[$name]) && $input[$name] === '1';
                continue;
            }

            $raw = $input[$name] ?? '';
            $value = is_string($raw) ? trim($raw) : '';
            $required = $f['required'] ?? true;
            if ($required === 'create') {
                $required = $isCreate;
            }

            if ($value === '') {
                if ($required) {
                    $errors[$name] = "$label is required.";
                } elseif ($type !== 'password') {
                    $clean[$name] = null;
                }
                continue;
            }

            switch ($type) {
                case 'text':
                case 'tel':
                    if (mb_strlen($value) > ($f['max'] ?? 255)) {
                        $errors[$name] = "$label must not exceed " . $f['max'] . ' characters.';
                    } elseif (isset($f['pattern']) && !preg_match($f['pattern'], $value)) {
                        $errors[$name] = $f['pattern_message'] ?? "$label has an invalid format.";
                    } else {
                        $clean[$name] = $value;
                    }
                    break;

                case 'number':
                    $isInt = $f['int'] ?? false;
                    $regex = $isInt ? '/^\d+$/' : '/^\d+(\.\d{1,2})?$/';
                    if (!preg_match($regex, $value)) {
                        $errors[$name] = $isInt
                            ? "$label must be a whole number."
                            : "$label must be a valid number with up to 2 decimal places.";
                    } elseif ((float) $value < ($f['min'] ?? 0) || (isset($f['max']) && (float) $value > $f['max'])) {
                        $min = $f['min'] ?? 0;
                        $errors[$name] = isset($f['max'])
                            ? "$label must be between $min and {$f['max']}."
                            : "$label must be at least $min.";
                    } elseif (isset($f['exclusive_min']) && (float) $value <= $f['exclusive_min']) {
                        $errors[$name] = "$label must be greater than {$f['exclusive_min']}.";
                    } else {
                        $clean[$name] = $isInt ? (int) $value : $value;
                    }
                    break;

                case 'date':
                    $d = DateTime::createFromFormat('Y-m-d', $value);
                    if (!$d || $d->format('Y-m-d') !== $value) {
                        $errors[$name] = "$label must be a valid date (YYYY-MM-DD).";
                    } else {
                        $clean[$name] = $value;
                    }
                    break;

                case 'select':
                    if (!ctype_digit($value) || !$lookup->exists($f['lookup'], (int) $value)) {
                        $errors[$name] = "Please choose a valid $label.";
                    } else {
                        $clean[$name] = (int) $value;
                    }
                    break;

                case 'password':
                    if (strlen($value) < 8 || !preg_match('/[A-Za-z]/', $value) || !preg_match('/\d/', $value)) {
                        $errors[$name] = "$label must be at least 8 characters and contain letters and numbers.";
                    } else {
                        $clean[$name] = $value;
                    }
                    break;
            }
        }

        return [$clean, $errors];
    }
}
