<?php

namespace Illuminate\Http;

interface Request
{
    /**
     * @return \App\Models\Member|null
     */
    public function user($guard = null);
}