<?php

namespace Deployer;

require 'recipe/common.php';
require 'recipe/rsync.php';
require 'recipe/cachetool.php';

//
// Project name
set('application', 'Catch A Guide Website');
set('keep_releases', 2);

//
// Hosts
inventory(__DIR__ . '/deploy.yml');

//
// Directory Configuration
set('shared_files', ['.env']);
set('rsync_src', realpath(__DIR__ . '/..'));
set('rsync_dest', '{{release_path}}');

//
// Exclude config
set('rsync', [
    'exclude'       => [
        '.git/',
        '/.ddev/',
        '/deploy/',
        '/devtools/',
        '/.local_htaccess',
        '/.env.local',
        '/.env.example',
        '/.gitignore',
        '/.gitlab-ci.yml',
        '/composer.*',
        '/README*.md',
    ],
    'exclude-file'  => false,
    'include'       => [],
    'include-file'  => false,
    'filter'        => [],
    'filter-file'   => false,
    'filter-perdir' => false,
    'flags'         => 'arzP',
    'options'       => ['delete'],
    'timeout'       => 300,
]);

//
// Main Tasks
task('project:general', function () {

});
// Apache Specific Tasks
task('project:apache', function () {
//    run('{{bin/symlink}} {{release_path}}/web/{{htaccess_name}} {{release_path}}/web/.htaccess');
})->onRoles('apache');
// Nginx Specific Tasks
task('project:nginx', function () {

})->onRoles('nginx');
// Apache Specific Tasks
task('project:deploy', function () {
    invoke('project:apache');
    invoke('project:nginx');
    invoke('project:general');
});

# *****************************************************************************
# * CacheTool Installer
# *****************************************************************************
task('cachetool:install', function () {
    $releasePath = get('release_path');
    cd($releasePath);
    run("curl -s https://gordalina.github.io/cachetool/downloads/cachetool-7.0.0.phar --output cachetool.phar");
});

//
// OpCache Tasks
task('project:opc_reset', function () {
    run("php74 -r 'opcache_reset();'");
})->onRoles('opc-reset');

task('project:opcache:web', function () {
    set('cachetool_args', '--web --web-path={{release_path}}/web --web-url={{public_url}}');
})->onRoles('opcode-web');
task('project:opcache:socket', function () {
//    run("socket_path=$(find /tmp -name *.sock)");
    set('cachetool_args', '--fcgi={{socket_path}}');
})->onRoles('opcode-socket');
task('project:opcache', function () {
    invoke('cachetool:install');
    invoke('project:opcache:web');
    invoke('project:opcache:socket');
    invoke('cachetool:clear:opcache');
})->onRoles('opcode-web', 'opcode-socket');

//
// Main Execution
task('project:release', function () {
    invoke('deploy:prepare');
    invoke('deploy:lock');
    invoke('deploy:release');
    invoke('rsync:warmup');
    invoke('rsync');
    invoke('deploy:shared');
    invoke('deploy:clear_paths');
    invoke('project:deploy');
    invoke('deploy:symlink');
    invoke('deploy:unlock');
//    invoke('project:opc_reset');
//    invoke('project:opcache');
});
task('deploy', function () {
    invoke('project:release');
    invoke('cleanup');
    invoke('success');
});
