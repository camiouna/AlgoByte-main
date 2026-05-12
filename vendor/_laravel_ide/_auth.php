<?php

namespace Illuminate\Contracts\Auth;

interface Guard
{
    /**
     * @return \App\Models\Member|null
     */
    public function user();
}