<?php

use Laravel\Octane\Contracts\OperationTerminated;
use Laravel\Octane\Events\RequestHandled;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\TaskReceived;
use Laravel\Octane\Events\TaskTerminated;
use Laravel\Octane\Events\TickReceived;
use Laravel\Octane\Events\TickTerminated;
use Laravel\Octane\Events\WorkerErrorOccurred;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Events\WorkerStopping;
use Laravel\Octane\Listeners\CloseMonologHandlers;
use Laravel\Octane\Listeners\CollectGarbage;
use Laravel\Octane\Listeners\DisconnectFromDatabases;
use Laravel\Octane\Listeners\EnsureUploadedFilesAreValid;
use Laravel\Octane\Listeners\EnsureUploadedFilesCanBeMoved;
use Laravel\Octane\Listeners\FlushOnce;
use Laravel\Octane\Listeners\FlushTemporaryContainerInstances;
use Laravel\Octane\Listeners\FlushUploadedFiles;
use Laravel\Octane\Listeners\ReportException;
use Laravel\Octane\Listeners\StopWorkerIfNecessary;
use Laravel\Octane\Octane;

return [

    /*
    |--------------------------------------------------------------------------
    | Octane Server
    |--------------------------------------------------------------------------
    |
    | This value determines the default "server" that will be used by Octane
    | when starting, restarting, or stopping your server via the CLI. You
    | are free to change this to the supported server of your choosing.
    |
    | Supported: "roadrunner", "swoole", "frankenphp"
    |
    */

    'server' => env('OCTANE_SERVER', 'roadrunner'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    |
    | When this configuration value is set to "true", Octane will inform the
    | framework that all absolute links must be generated using the HTTPS
    | protocol. Otherwise your links may be generated using plain HTTP.
    |
    */

    'frankenphp' => [
        'worker_count' => PHP_OS_FAMILY === 'Windows'
            ? 1
            : max(1, (int) (shell_exec('nproc') * 0.75)), // Use 75% of CPU cores
        'max_requests' => 1000,
    ],

    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Octane Listeners
    |--------------------------------------------------------------------------
    |
    | All of the event listeners for Octane's events are defined below. These
    | listeners are responsible for resetting your application's state for
    | the next request. You may even add your own listeners to the list.
    |
    */

    'listeners' => [
        WorkerStarting::class => [
            EnsureUploadedFilesAreValid::class,
            EnsureUploadedFilesCanBeMoved::class,
        ],

        RequestReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            ...Octane::prepareApplicationForNextRequest(),
            //
        ],

        RequestHandled::class => [
            //
        ],

        RequestTerminated::class => [
            // FlushUploadedFiles::class,
        ],

        TaskReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            //
        ],

        TaskTerminated::class => [
            //
        ],

        TickReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            //
        ],

        TickTerminated::class => [
            //
        ],

        OperationTerminated::class => [
            FlushOnce::class,
            FlushTemporaryContainerInstances::class,
            // DisconnectFromDatabases::class,
            // CollectGarbage::class,
        ],

        WorkerErrorOccurred::class => [
            ReportException::class,
            StopWorkerIfNecessary::class,
        ],

        WorkerStopping::class => [
            CloseMonologHandlers::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Warm / Flush Bindings
    |--------------------------------------------------------------------------
    |
    | The bindings listed below will either be pre-warmed when a worker boots
    | or they will be flushed before every new request. Flushing a binding
    | will force the container to resolve that binding again when asked.
    |
    */

    'warm' => [
        ...Octane::defaultServicesToWarm(),
    ],

    'flush' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Swoole Tables
    |--------------------------------------------------------------------------
    |
    | While using Swoole, you may define additional tables as required by the
    | application. These tables can be used to store data that needs to be
    | quickly accessed by other workers on the particular Swoole server.
    |
    */

    'tables' => [
        'example:1000' => [
            'name'  => 'string:1000',
            'votes' => 'int',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Swoole Cache Table
    |--------------------------------------------------------------------------
    |
    | While using Swoole, you may leverage the Octane cache, which is powered
    | by a Swoole table. You may set the maximum number of rows as well as
    | the number of bytes per row using the configuration options below.
    |
    */

    'cache' => [
        'rows'  => 1000,
        'bytes' => 10000,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Watching
    |--------------------------------------------------------------------------
    |
    | The following list of files and directories will be watched when using
    | the --watch option offered by Octane. If any of the directories and
    | files are changed, Octane will automatically reload your workers.
    |
    */

    'watch' => [
        'app',
        'bootstrap',
        'config/**/*.php',
        'database/**/*.php',
        'public/**/*.php',
        'resources/**/*.php',
        'routes',
        'composer.lock',
        '.env',
    ],

    /*
    |--------------------------------------------------------------------------
    | Garbage Collection Threshold
    |--------------------------------------------------------------------------
    |
    | When executing long-lived PHP scripts such as Octane, memory can build
    | up before being cleared by PHP. You can force Octane to run garbage
    | collection if your application consumes this amount of megabytes.
    |
    */

    'garbage' => 50,

    /*
    |--------------------------------------------------------------------------
    | Maximum Execution Time
    |--------------------------------------------------------------------------
    |
    | The following setting configures the maximum execution time for requests
    | being handled by Octane. You may set this value to 0 to indicate that
    | there isn't a specific time limit on Octane request execution time.
    |
    */

    'max_execution_time' => 30,

    'swoole' => [
        // Worker processes (handles HTTP requests)
        'worker_count' => env('SWOOLE_WORKER_COUNT', max(8, 2 * 4)),

        // Task workers (handles async tasks)
        'task_worker_count' => env('SWOOLE_TASK_WORKER_COUNT', 2),

        // Max requests before worker restart (prevents memory leaks)
        'max_request'       => env('SWOOLE_MAX_REQUEST', 1000),
        'max_request_grace' => env('SWOOLE_MAX_REQUEST_GRACE', 100),

        // Socket backlog size
        'backlog' => env('SWOOLE_BACKLOG', 8192),

        // Max connections
        'max_conn' => env('SWOOLE_MAX_CONN', 10000),

        // Enable coroutine support
        'enable_coroutine' => true,

        // Static file handling
        'document_root'         => base_path('public'),
        'enable_static_handler' => env('SWOOLE_STATIC_HANDLER', false),

        // Performance optimizations
        'buffer_output_size' => 32 * 1024 * 1024, // 32MB
        'socket_buffer_size' => 128 * 1024 * 1024, // 128MB

        // HTTP/2 support
        'open_http2_protocol' => true,

        // Compression
        'http_compression'       => true,
        'http_compression_level' => 6,

        // Logging
        'log_file'  => storage_path('logs/swoole.log'),
        'log_level' => 'warning',

        // Daemon mode for production
        'daemonize' => env('APP_ENV') === 'production',
        'pid_file'  => storage_path('logs/swoole.pid'),

        // Process user/group
        'user'  => env('SWOOLE_USER', get_current_user()),
        'group' => env('SWOOLE_GROUP', get_current_user()),

        // Reactor thread count
        'reactor_num' => 2,

        // Worker memory limits
        'worker_max_memory' => 256 * 1024 * 1024, // 256MB per worker

        // Enable CPU affinity
        'enable_cpu_affinity' => true,

        // TCP fast open
        'tcp_fastopen' => true,

        // Other options
        'options' => [
            'max_coroutine'          => 300000,
            'socket_connect_timeout' => 5,
            'socket_timeout'         => 60,
            'ssl_cert_file'          => env('SWOOLE_SSL_CERT'),
            'ssl_key_file'           => env('SWOOLE_SSL_KEY'),
            'reload_async'           => true,
            'tcp_user_timeout'       => 60,
        ],
    ],

];
