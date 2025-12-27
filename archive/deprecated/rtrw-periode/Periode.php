<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * DEPRECATED: Periode module removed. Stub to make accidental instantiation fail.
 */
class Periode extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        throw new RuntimeException('Periode model has been removed. If you need to restore it, re-run migrations or check the archive.');
    }
}
