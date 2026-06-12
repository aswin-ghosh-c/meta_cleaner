<?php

use App\Models\User;
use App\Providers\AppServiceProvider;

it('returns a successful response from homepage', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

it('forces https scheme in production', function () {
    $originalEnv = app()->environment();
    
    // Simulate production environment
    app()->detectEnvironment(fn() => 'production');
    
    $provider = new AppServiceProvider(app());
    $provider->boot();
    
    // Check if scheme is forced to https
    expect(url()->to('/'))->toStartWith('https://');
    
    // Restore environment
    app()->detectEnvironment(fn() => $originalEnv);
});

it('can create a user model', function () {
    $user = new User([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
    
    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
});
