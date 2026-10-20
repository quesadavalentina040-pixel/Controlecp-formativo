<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Lightweight form builder that replaces laravelcollective/html.
 * Generates standard Bootstrap-compatible form HTML.
 */
class FormBuilder
{
    protected ?string $modelClass = null;
    protected ?string $modelKey = null;

    /**
     * Open a form element.
     */
    public function open(array $options = []): string
    {
        $method = $options['method'] ?? 'POST';
        $url = $options['url'] ?? '';
        $class = $options['class'] ?? '';
        $files = isset($options['files']) && $options['files'] ? ' enctype="multipart/form-data"' : '';
        $id = isset($options['id']) ? ' id="' . e($options['id']) . '"' : '';
        $attributes = '';

        // Splice out known options, pass the rest as HTML attributes
        $knownKeys = ['method', 'url', 'route', 'class', 'files', 'id', 'model'];
        foreach ($options as $key => $value) {
            if (!in_array($key, $knownKeys)) {
                $attributes .= ' ' . e($key) . '="' . e($value) . '"';
            }
        }

        // Handle route option
        if (isset($options['route'])) {
            $url = is_array($options['route'])
                ? route($options['route'][0], $options['route'][1] ?? [])
                : route($options['route']);
        }

        // Normalize method
        $method = strtoupper($method);
        $formMethod = $method;
        $methodField = '';
        if (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            $formMethod = 'POST';
            $methodField = '<input type="hidden" name="_method" value="' . $method . '">';
        }

        // Store model if provided
        if (isset($options['model'])) {
            $this->modelClass = get_class($options['model']);
        }

        return '<form method="' . strtolower($formMethod) . '" action="' . e($url) . '"' .
            ($class ? ' class="' . e($class) . '"' : '') .
            $files . $id . $attributes . '>' .
            $methodField .
            csrf_field();
    }

    /**
     * Close a form element.
     */
    public function close(): string
    {
        $this->modelClass = null;
        return '</form>';
    }

    /**
     * Generate a label element.
     */
    public function label(string $for, string $text, array $options = []): HtmlString
    {
        $class = $options['class'] ?? '';
        return new HtmlString(
            '<label for="' . e($for) . '"' . ($class ? ' class="' . e($class) . '"' : '') . '>' . e($text) . '</label>'
        );
    }

    /**
     * Generate a text input.
     */
    public function text(string $name, mixed $value = null, array $options = []): HtmlString
    {
        return $this->input('text', $name, $value, $options);
    }

    /**
     * Generate a number input.
     */
    public function number(string $name, mixed $value = null, array $options = []): HtmlString
    {
        return $this->input('number', $name, $value, $options);
    }

    /**
     * Generate an email input.
     */
    public function email(string $name, mixed $value = null, array $options = []): HtmlString
    {
        return $this->input('email', $name, $value, $options);
    }

    /**
     * Generate a password input.
     */
    public function password(string $name, array $options = []): HtmlString
    {
        return $this->input('password', $name, null, $options);
    }

    /**
     * Generate a hidden input.
     */
    public function hidden(string $name, mixed $value = null, array $options = []): HtmlString
    {
        return $this->input('hidden', $name, $value, $options);
    }

    /**
     * Generate a textarea.
     */
    public function textarea(string $name, mixed $value = null, array $options = []): HtmlString
    {
        $class = $options['class'] ?? 'form-control';
        $attributes = $this->buildAttributes($name, $options, $class);
        $value = old($name, $value);

        return new HtmlString(
            '<textarea name="' . e($name) . '"' . $attributes . '>' . e($value ?? '') . '</textarea>'
        );
    }

    /**
     * Generate a select element.
     */
    public function select(string $name, mixed $list = [], mixed $selected = null, array $options = []): HtmlString
    {
        $class = $options['class'] ?? 'form-control';
        $placeholder = $options['placeholder'] ?? null;
        unset($options['placeholder'], $options['class']);

        $selected = old($name, $selected);

        $html = '<select name="' . e($name) . '" class="' . e($class) . '"';
        foreach ($options as $key => $value) {
            if (is_bool($value)) {
                if ($value) $html .= ' ' . e($key);
            } else {
                $html .= ' ' . e($key) . '="' . e($value) . '"';
            }
        }
        $html .= '>';

        if ($placeholder) {
            $html .= '<option value=""' . ($selected === null ? ' selected' : '') . '>' . e($placeholder) . '</option>';
        }

        if (is_array($list) || $list instanceof \Illuminate\Support\Collection) {
            foreach ($list as $value => $label) {
                $sel = ((string)$selected === (string)$value) ? ' selected' : '';
                $html .= '<option value="' . e($value) . '"' . $sel . '>' . e($label) . '</option>';
            }
        }

        $html .= '</select>';

        return new HtmlString($html);
    }

    /**
     * Generate a submit button.
     */
    public function submit(string $value = null, array $options = []): HtmlString
    {
        $class = $options['class'] ?? 'btn btn-primary';
        unset($options['class']);

        $html = '<button type="submit" class="' . e($class) . '"';
        foreach ($options as $key => $val) {
            if (is_bool($val)) {
                if ($val) $html .= ' ' . e($key);
            } else {
                $html .= ' ' . e($key) . '="' . e($val) . '"';
            }
        }
        $html .= '>' . e($value ?? 'Submit') . '</button>';

        return new HtmlString($html);
    }

    /**
     * Generate a button.
     */
    public function button(string $value = null, array $options = []): HtmlString
    {
        $class = $options['class'] ?? 'btn btn-secondary';
        $type = $options['type'] ?? 'button';
        unset($options['class'], $options['type']);

        $html = '<button type="' . e($type) . '" class="' . e($class) . '"';
        foreach ($options as $key => $val) {
            if (is_bool($val)) {
                if ($val) $html .= ' ' . e($key);
            } else {
                $html .= ' ' . e($key) . '="' . e($val) . '"';
            }
        }
        $html .= '>' . e($value ?? '') . '</button>';

        return new HtmlString($html);
    }

    /**
     * Generate a file input.
     */
    public function file(string $name, array $options = []): HtmlString
    {
        return $this->input('file', $name, null, $options);
    }

    /**
     * Generate a checkbox.
     */
    public function checkbox(string $name, mixed $value = 1, mixed $checked = null, array $options = []): HtmlString
    {
        $class = $options['class'] ?? 'form-check-input';
        unset($options['class']);
        $isChecked = old($name, $checked);
        $checkedAttr = $isChecked ? ' checked' : '';

        $html = '<input type="checkbox" name="' . e($name) . '" value="' . e($value) . '" class="' . e($class) . '"' . $checkedAttr;
        foreach ($options as $key => $val) {
            if (is_bool($val)) {
                if ($val) $html .= ' ' . e($key);
            } else {
                $html .= ' ' . e($key) . '="' . e($val) . '"';
            }
        }
        $html .= '>';

        return new HtmlString($html);
    }

    /**
     * Generate a radio button.
     */
    public function radio(string $name, mixed $value = null, mixed $checked = null, array $options = []): HtmlString
    {
        $class = $options['class'] ?? 'form-check-input';
        unset($options['class']);
        $isChecked = old($name) === $value || $checked;
        $checkedAttr = $isChecked ? ' checked' : '';

        $html = '<input type="radio" name="' . e($name) . '" value="' . e($value) . '" class="' . e($class) . '"' . $checkedAttr;
        foreach ($options as $key => $val) {
            if (is_bool($val)) {
                if ($val) $html .= ' ' . e($key);
            } else {
                $html .= ' ' . e($key) . '="' . e($val) . '"';
            }
        }
        $html .= '>';

        return new HtmlString($html);
    }

    /**
     * Core input generator.
     */
    protected function input(string $type, string $name, mixed $value = null, array $options = []): HtmlString
    {
        $class = $options['class'] ?? 'form-control';
        unset($options['class']);

        // Use old() for sticky input unless readonly
        $val = old($name, $value);
        $valueAttr = $val !== null ? ' value="' . e($val) . '"' : '';

        $html = '<input type="' . e($type) . '" name="' . e($name) . '"' . $valueAttr . ' class="' . e($class) . '"';
        foreach ($options as $key => $v) {
            if (is_bool($v)) {
                if ($v) $html .= ' ' . e($key);
            } else {
                $html .= ' ' . e($key) . '="' . e($v) . '"';
            }
        }
        $html .= '>';

        return new HtmlString($html);
    }

    /**
     * Build common HTML attributes for an element.
     */
    protected function buildAttributes(string $name, array $options, string $class = 'form-control'): string
    {
        $attributes = ' class="' . e($class) . '"';
        foreach ($options as $key => $value) {
            if ($key === 'class') continue;
            if (is_bool($value)) {
                if ($value) $attributes .= ' ' . e($key);
            } else {
                $attributes .= ' ' . e($key) . '="' . e($value) . '"';
            }
        }
        return $attributes;
    }
}
