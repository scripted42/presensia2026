<?php

// Script to fix UserSeeder to use firstOrCreate instead of create

$file = 'database/seeders/UserSeeder.php';
$content = file_get_contents($file);

// Replace all User::create with User::firstOrCreate
$content = preg_replace(
    '/User::create\(\[/',
    'User::firstOrCreate([' . PHP_EOL . '            [\'email\' => \'$email\'],' . PHP_EOL . '            [',
    $content
);

// This is a complex replacement, let's do it manually
echo "Manual replacement needed for UserSeeder.php\n";
echo "Replace all User::create([ with User::firstOrCreate(['email' => 'email_value'], [\n";
