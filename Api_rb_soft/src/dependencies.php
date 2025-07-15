<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

$container['db15'] = function ($c) {
    $dbSettings = $c->get('settings')['db15'];
    $dsn = 'mysql:host=' . $dbSettings['host'] . ';dbname=' . $dbSettings['dbname'] . ';charset=' . $dbSettings['charset'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};

$container['db14'] = function ($c) {
    $dbSettings = $c->get('settings')['db14'];
    $dsn = 'mysql:host=' . $dbSettings['host'] . ';dbname=' . $dbSettings['dbname'] . ';charset=' . $dbSettings['charset'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};

$container['db16'] = function ($c) {
    $dbSettings = $c->get('settings')['db16'];
    $dsn = 'mysql:host=' . $dbSettings['host'] . ';dbname=' . $dbSettings['dbname'] . ';charset=' . $dbSettings['charset'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};


$container['db18'] = function ($c) {
    $dbSettings = $c->get('settings')['db18'];
    $dsn = 'mysql:host=' . $dbSettings['host'] . ';dbname=' . $dbSettings['dbname'] . ';charset=' . $dbSettings['charset'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};


//* Agregado el 16-05-2025
$container['dbMP'] = function ($c) {
    $dbSettings = $c->get('settings')['dbMP'];
    // $dsn = 'mysql:host=' . $dbSettings['host'] . ';dbname=' . $dbSettings['dbname'] . ';charset=' . $dbSettings['charset'];
    //* Agregado el 18-12-2024
    $dsn = 'mysql:host=' . $dbSettings['host'] . ';port=' . $dbSettings['port'] . ';dbname=' . $dbSettings['dbname'] . ';charset=' . $dbSettings['charset'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};

$container['db250HF'] = function ($c) {
    $dbSettings = $c->get('settings')['db250HF'];
    $dsn = 'mysql:host=' . $dbSettings['host'] . ';dbname=' . $dbSettings['dbname'] . ';charset=' . $dbSettings['charset'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};

$container['db250LL'] = function ($c) {
    $dbSettings = $c->get('settings')['db250LL'];
    $dsn = 'mysql:host=' . $dbSettings['host'] . ';dbname=' . $dbSettings['dbname'] . ';charset=' . $dbSettings['charset'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};

$container['db250LF'] = function ($c) {
    $dbSettings = $c->get('settings')['db250LF'];
    $dsn = 'mysql:host=' . $dbSettings['host'] . ';dbname=' . $dbSettings['dbname'] . ';charset=' . $dbSettings['charset'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};


$container['dbSOFTLAND_DEV'] = function ($c) {
    $dbSettings = $c->get('settings')['dbSOFTLAND_DEV'];
    $dsn = 'sqlsrv:Server=' . $dbSettings['host'] . ';Database=' . $dbSettings['dbname'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};


$container['dbSOFTLAND_PROD'] = function ($c) {
    $dbSettings = $c->get('settings')['dbSOFTLAND_PROD'];
    $dsn = 'sqlsrv:Server=' . $dbSettings['host'] . ';Database=' . $dbSettings['dbname'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};

$container['dbSOFTLAND_PROD_HF'] = function ($c) {
    $dbSettings = $c->get('settings')['dbSOFTLAND_PROD_HF'];
    $dsn = 'sqlsrv:Server=' . $dbSettings['host'] . ';Database=' . $dbSettings['dbname'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbSettings['user'], $dbSettings['pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
};


//* Agregado el 26-10-2023
$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        return $response->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-type', 'text/html')
            ->write('Method must be one of: ' . implode(', ', $methods));
    };
};



