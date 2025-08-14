<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use TronderData\TdCostCalcultaror\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class MultilingualTest extends TestCase
{
    /** @test */
    public function it_sets_locale_from_request_parameter()
    {
        // Set available languages in config
        Config::set('td-cost-calculator.languages.available', [
            'en' => 'English',
            'no' => 'Norwegian'
        ]);
        
        // Create a request with locale parameter
        $request = new Request();
        $request->merge(['locale' => 'no']);
        
        // Create the middleware
        $middleware = new SetLocale();
        
        // Execute the middleware
        $middleware->handle($request, function ($req) {
            // Verify the locale was set correctly
            $this->assertEquals('no', App::getLocale());
            return $req;
        });
    }
    
    /** @test */
    public function it_defaults_to_english_for_invalid_locale()
    {
        // Set default locale
        App::setLocale('en');
        
        // Set available languages in config
        Config::set('td-cost-calculator.languages.available', [
            'en' => 'English',
            'no' => 'Norwegian'
        ]);
        
        // Create a request with invalid locale parameter
        $request = new Request();
        $request->merge(['locale' => 'invalid-locale']);
        
        // Create the middleware
        $middleware = new SetLocale();
        
        // Execute the middleware
        $middleware->handle($request, function ($req) {
            // Verify the locale was not changed
            $this->assertEquals('en', App::getLocale());
            return $req;
        });
    }
    
    /** @test */
    public function it_uses_session_locale_when_request_locale_is_not_provided()
    {
        // Set available languages in config
        Config::set('td-cost-calculator.languages.available', [
            'en' => 'English',
            'no' => 'Norwegian'
        ]);
        
        // Set session locale
        session(['locale' => 'no']);
        
        // Create a request without locale parameter
        $request = new Request();
        
        // Create the middleware
        $middleware = new SetLocale();
        
        // Execute the middleware
        $middleware->handle($request, function ($req) {
            // Verify the locale was set from session
            $this->assertEquals('no', App::getLocale());
            return $req;
        });
    }
    
    /** @test */
    public function it_has_translations_for_key_messages()
    {
        // Test English translations
        App::setLocale('en');
        $this->assertEquals('Cost Calculator', __('td-cost-calcultaror::messages.module_name'));
        $this->assertEquals('Clear Cache', __('td-cost-calcultaror::messages.clear_cache'));
        
        // Test Norwegian translations
        App::setLocale('no');
        $this->assertEquals('Kostnadsberegner', __('td-cost-calcultaror::messages.module_name'));
        $this->assertEquals('Tøm Cache', __('td-cost-calcultaror::messages.clear_cache'));
    }
}
