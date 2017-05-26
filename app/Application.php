<?php
########################################################
#                                                      #
# Create DI container. Load Configs, Services, and DB. #
#                                                      #
########################################################

$app = new \Pimple\Container();

/**
 * Config files.
 */
$yaml = new \Symfony\Component\Yaml\Parser();
$services = $yaml->parse(file_get_contents(__DIR__ . '/../config/services.yml'));
$app['config'] = $yaml->parse(file_get_contents(__DIR__ . '/../config/settings.yml'));

// Credentials are not included in the Git repository.
if (is_readable(__DIR__ . '/../config/credentials.yml')) {
    $credentials = $yaml->parse(file_get_contents(__DIR__ . '/../config/credentials.yml'));
    foreach ($credentials as $name => $values) {
        $app['credential.' . $name] = (object)$values;
    }
}

/**
 * Services
 */
foreach ($services as $service => $data) {
    $app[$service] = function ($app) use ($data) {
        $args = array();
        if (isset($data['args'])) {
            foreach ($data['args'] as $dependency) {
                // Config dependencies
                if (is_array($dependency)) {
                    $config = $app['config'];
                    foreach ($dependency as $key) {
                        $args[] = $config[$key];
                    }
                // Service dependencies
                } else {
                    $args[] = $app[$dependency];
                }
            }
        }
        $reflection = new ReflectionClass($data['class']);
        return $reflection->newInstanceArgs($args);
    };
}

/**
 * Target-Specific Logging classes
 * Create more logging classes as more targets are added
 *
 * @return \Monolog\Logger
 */
$app['logger.factory'] =  $app->protect(function ($name, $handler) use ($app) {
    $logger = new Monolog\Logger(ucfirst($name));

    // Write to log file if handler is a string.
    if (is_string($handler)) {
        $logPath = $handler;
        $handler = new \Monolog\Handler\StreamHandler(__DIR__."/../".$logPath, \Monolog\Logger::DEBUG);
    }

    if (is_object($handler)) {
        $logger->pushHandler($handler);
    }

    if ($app['config']['logger']['echo']) {
        $logger->pushHandler(new \Monolog\Handler\EchoHandler());
    }
    return $logger;
});

$app['import.logger'] = function ($app) {
    return $app['logger.factory']('Import', $app['config']['logger']['import']['streamHandler']);
};

/**
 * Database connection.
 */
$app['db'] = function ($app) {
    $credential = $app['credential.db'];
    include_once(__DIR__ . '/PDOWrapper.php');
    $db = new \App\PDOWrapper(
        "mysql:host={$credential->host};dbname={$credential->dbname}",
        $credential->user,
        $credential->password,
        array(
            // PDO throws exceptions
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // fetch methods return srdClass
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        )
    );
    return $db;
};

return $app;
