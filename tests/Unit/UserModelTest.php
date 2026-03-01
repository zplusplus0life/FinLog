<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;


class UserModelTest extends TestCase
{
    
    use RefreshDatabase;

    /** @test */
    public function user_can_be_created_with_valid_data()
    {
       $user = User::create([
        'nama_lengkap' => 'john cena',
        'email' => 'cena@example.com',
        'password' => Hash::make('pasword123'),
        'role' => 'staff',
        'status' => true,
       ]);

       $this->assertNotNull($user->id);
       $this->assertEquals('john cena', $user->nama_lengkap);
       $this->assertEquals('staff', $user->role);
       $this->assertEquals(true, $user->status);
    }

    /** @test */
    public function user_password_is_hashed()
    {
        $plainPassword = 'passwordAman123';

        $user = User::create([
            'nama_lengkap' => 'john cena',
            'email' => 'john@cena.com',
            'password' => Hash::make($plainPassword),
            'role' => 'staff',
        ]);

        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }


    /** @test */
    public function user_can_store_all_valid_roles()
    {
        $role = ['admin', 'staff', 'manajer'];

        foreach($role as $roles){
            $user = User::factory()->create(['role' => $roles]);
            $this->assertEquals($roles, $user->role);
        }
    }


    /** @test */
    public function user_can_be_activated()
    {
        $user = User::factory()->create(['status' => false]);

        $user->update(['status' => true]);

        $this->assertTrue($user->status);
    }

    /** @test */
    public function user_can_be_deactivated()
    {
        $user = User::factory()->create(['status' => true]);

        $user->update(['status' => false]);

        $this->assertFalse($user->status);
    }


    /** @test */
    public function user_has_all_fillable_attributes()
    {
        $fillable = [
            'nama_lengkap',
            'email',
            'password',
            'role',
            'status',
        ];

        $dataUser = (new User())->getFillable();

        forEach($fillable as $attributes){
            $this->assertContains($attributes, $dataUser);
        }
    }
}
