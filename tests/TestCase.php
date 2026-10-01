<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $dbName = DB::connection()->getDatabaseName();

        if ($dbName === 'sistema_ibc_oficial' || !str_contains($dbName, 'testing')) {
            throw new \RuntimeException(
                "¡PROTECCIÓN DE TESTING ACTIVADA! Los tests intentaron ejecutarse sobre la base de datos oficial [{$dbName}]. " .
                "La base de datos permitida para testing debe ser 'sistema_ibc_oficial_testing'."
            );
        }
    }
}
