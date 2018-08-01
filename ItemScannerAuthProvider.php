<?php

namespace Modules\ItemScanner\;

use App\Providers\AuthServiceProvider;

class ItemscannerAuthProvider extends AuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \Modules\Itemscanner\Models\Itemscanner::class => \Modules\Itemscanner\Policies\ItemscannerPolicy::class,
    ];
}
