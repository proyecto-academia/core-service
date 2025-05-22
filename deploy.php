<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config
set('keep_releases', 3);
set('application', 'core-service');
set('repository', 'git@github.com:proyecto-academia/core-service.git');


set('branch', 'main');


add('shared_dirs', ['storage', 'bootstrap/cache']);
add('shared_files', ['.env']);
add('writable_dirs', ['storage', 'bootstrap/cache']);



host('56.228.50.178')
    ->set('remote_user', 'deployer')
    ->set('identity_file', '/home/mark/.ssh/id_rsa')
    ->set('deploy_path', '/var/www/laravel');


// Hooks
// task('build', function () {
//     run('cd {{release_path}} && npm install && npm run build');
// });


task('upload:env', function () {
    upload('.env.production', '{{deploy_path}}/shared/.env');
})->desc('Environment setup');

task('artisan:cache:clear', function () {
    run('{{release_path}}/artisan cache:clear');
});

task('artisan:route:clear', function () {
    run('{{release_path}}/artisan route:clear');
});

task('artisan:view:clear', function () {
    run('{{release_path}}/artisan view:clear');
});

task('artisan:config:clear', function () {
    run('{{release_path}}/artisan config:clear');
});

task('php-fpm:restart', function () {
    run('sudo systemctl restart php8.3-fpm');
});

// task('php artisan l5-swagger:generate', function () {
//     run('cd {{release_path}} && sudo php artisan l5-swagger:generate');
// });



after('deploy:shared', 'upload:env');



after('deploy:failed', 'deploy:unlock');
after('deploy:symlink', 'artisan:cache:clear');
after('deploy:symlink', 'artisan:route:clear');
after('deploy:symlink', 'artisan:view:clear');
after('deploy:symlink', 'artisan:config:clear');
after('deploy:symlink', 'php-fpm:restart');
// after('deploy:symlink', 'build');
// after('deploy:symlink', 'php artisan l5-swagger:generate');


before('deploy:symlink', 'artisan:migrate');