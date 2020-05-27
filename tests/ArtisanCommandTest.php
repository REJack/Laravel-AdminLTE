<?php

use JeroenNoten\LaravelAdminLte\Console\AdminLteStatusCommand;

class StatusTest extends TestCase
{
    var $plugins;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testStatusOnly()
    {
        $command = $this->artisan('adminlte:status');
        $command->assertExitCode(0);
    }

    public function testBlank()
    {
        $configDir = base_path('config_test');
        $routesDir = base_path('routes');
        $routesFile = base_path('routes/web.php');

        if (!is_dir($configDir)) {
            mkdir($configDir);
        }
        if (!is_dir($routesDir)) {
            mkdir($routesDir);
        }
        if (file_exists($routesFile)) {
            unlink($routesFile);
            create_empty_file($routesFile);
        }


        $command = $this->artisan('adminlte:install --type=basic --with=auth_views --force');
        $command = $this->artisan('adminlte:status');
        $command->assertExitCode(0);
    }

    /**
     * @depends testBlank
     */
    public function testModified()
    {
        file_put_contents(
            base_path('config/adminlte.php'),
            '//MODDED',
            FILE_APPEND
        );

        $command = $this->artisan('adminlte:status --include-images');
        $command->assertExitCode(0);
    }

    /**
     * @depends testModified
     */
    public function testRemoved()
    {
        unlink(public_path('vendor/jquery/jquery.js'));
        unlink(public_path('vendor/jquery/jquery.min.js'));
        unlink(public_path('vendor/jquery/jquery.min.map'));
        unlink(public_path('vendor/adminlte/dist/js/adminlte.min.js'));
        unlink(public_path('vendor/adminlte/dist/css/adminlte.min.css'));

        $command = $this->artisan('adminlte:status --include-images');
        $command->assertExitCode(0);
    }
}
