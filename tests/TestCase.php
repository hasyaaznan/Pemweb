<?php

namespace Tests;

use App\Support\Klub;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Isi tabel pengaturan disimpan statis selama proses PHP berjalan; tiap test mulai dari database baru
        Klub::lupakan();
    }
}
