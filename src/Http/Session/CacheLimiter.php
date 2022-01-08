<?php

declare(strict_types=1);

namespace Neu\Http\Session;

enum CacheLimiter: string {
    case NoCache = 'nocache';
    case Public = 'public';
    case Private = 'private';
    case PrivateNoExpire = 'private_no_expire';
}
