<?php

namespace App\Support;

use Illuminate\Support\Facades\Facade;

/**
 * Minimal Form builder shim that provides the most commonly used
 * methods from laravelcollective/html so existing SICA blade views
 * keep working without installing the full package.
 *
 * @method static string open(array $options = [])
 * @method static string close()
 * @method static string label(string $for, string $text, array $options = [])
 * @method static string text(string $name, mixed $value = null, array $options = [])
 * @method static string number(string $name, mixed $value = null, array $options = [])
 * @method static string email(string $name, mixed $value = null, array $options = [])
 * @method static string password(string $name, array $options = [])
 * @method static string hidden(string $name, mixed $value = null, array $options = [])
 * @method static string textarea(string $name, mixed $value = null, array $options = [])
 * @method static string select(string $name, mixed $list = [], mixed $selected = null, array $options = [])
 * @method static string submit(string $value = null, array $options = [])
 * @method static string button(string $value = null, array $options = [])
 * @method static string file(string $name, array $options = [])
 * @method static string checkbox(string $name, mixed $value = 1, mixed $checked = null, array $options = [])
 * @method static string radio(string $name, mixed $value = null, mixed $checked = null, array $options = [])
 */
class Form extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'form.builder';
    }
}
