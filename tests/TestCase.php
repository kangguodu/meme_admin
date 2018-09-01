<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JWTFactory;
use JWTAuth;
use Carbon\Carbon;
use Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
