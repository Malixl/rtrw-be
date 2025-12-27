<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * DEPRECATED: RTRW module removed. This is a harmless stub to make accidental
 * instantiation fail loudly and guide developers to archived data if needed.
 */
class Rtrw extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        throw new RuntimeException('Rtrw model has been removed. If you need to restore it, re-run migrations or check the archive.');
    }
}
