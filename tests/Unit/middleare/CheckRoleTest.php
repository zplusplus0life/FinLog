<?php

namespace Tests\Unit\middleare;

use Tests\TestCase;
use App\Http\Middleware\CheckRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleTest extends TestCase
{
    
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        $this->artisan('migrate');
        
    }

    /** @test */
    public function guest_is_redirected_to_login()
    {
        $middleware = new Checkrole();

        $req = Request::create('/admin/dashboard', 'GET');

        $res = $middleware->handle($req, function(){
            return new Response('OK');
        }, 'admin');

        $this->assertEquals('302', $res->getStatusCode());
        $this->assertStringContainsString('login', $res->headers->get('Location'));
    }
}
