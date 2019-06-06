<?php
namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'planner');

// Project repository
set('repository', 'git@github.com:Kimla/planner.git');

set('git_tty', false);
set('ssh_multiplexing', false);

// Shared files/dirs between deploys 
set('shared_files', []);
set('shared_dirs', []);

// Writable dirs by web server 
set('writable_dirs', []);


// Hosts

host('165.227.147.160')
    ->user('root')
    ->set('deploy_path', '/var/www/test');    
    

/*
 * Make sure we are on the right branch
 */
task('deploy:checkout', function () {
    runLocally('git checkout '.get('branch', '').' 2> /dev/null');
})->desc('checking out git branch');
/*
 * Bring the app on the server down
 */
task('deploy:app_down', function () {
    run('php artisan down');
    run('php artisan cache:clear');
})->desc('bringing app down');
/*
 * Pull the changes onto the server
 */
task('deploy:pull_changes', function () {
    run('git pull origin '.get('branch', '').' 2> /dev/null');
    run('php artisan cache:clear');
})->desc('pull changes on server');
/*
 * Locally generate assets and transfer them to the server
 */
task('deploy:generate_assets', function () {
    runLocally('gulp compile');
    run('rm -rf public/assets/*');
    upload('public/assets/', 'public/assets/');
})->desc('generating assets');
/*
 * Run composer install on the server
 */
task('deploy:composer_install', function () {
    run('composer install');
})->desc('running composer install');
/*
 * Run the migrations on the server
 */
task('deploy:run_migrations', function () {
    run('php artisan db:backup');
    run('php artisan migrate --force --env=production');
})->desc('running migrations');
/*
 * Bring the app back up
 */
task('deploy:app_up', function () {
    run('php artisan cache:clear');
    run('php artisan up');
})->desc('bringing app up');

task('test', function () {
    run('ls');
    run('git pull');
})->desc('pull changes on server');

// Tasks

desc('Deploy your project');
task('deploy', [
    'test',
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
