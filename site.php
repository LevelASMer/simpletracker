<?php

$CONFIG = array(
    // Example PostgreSQL configuration
    'db' => array(
        'connection_string' => 'pgsql:host=localhost;dbname=simpletracker',
        'type' => 'pgsql',
        'user'=>'simpletracker',
        'password' => 'simpletracker',
    ),

    // Example MySQL configuration
    // 'db' => array(
    //     'connection_string' => 'mysql:host=localhost;dbname=simpletracker',
    //     'type' => 'mysql',
    //     'user'=>'simpletracker',
    //     'password' => 'simpletracker',
    // ),

    'base_url' => 'https://domain.xyz', // no trailing slash
    'site_title' => 'simpletracker',
    'max_torrent_size' => 20*1024*1024,
);

require_once 'db.php';
$db = $CONFIG['db']['type'] == 'mysql' ? new MySqlDatabase()
                                       : new PostgreSqlDatabase();

function html_escape($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function random_hash() {
    $s = openssl_random_pseudo_bytes(30);
    if ($s === null) {
        die('no source of randomness');
    }

    return md5($s);
}

function require_auth() {
    global $CONFIG;
    if (!array_key_exists('user', $_SESSION)) {
        header(sprintf('Location: %s/login.php', $CONFIG['base_url']));
        die;
    }
}

function check_csrf() {
    if (!array_key_exists('csrf', $_POST) || $_POST['csrf'] !== $_SESSION['csrf']) {
        die;
    }
}

function csrf_html() {
    printf('<input type="hidden", name="csrf" value="%s" />', html_escape($_SESSION['csrf']));
}

function gen_csrf($replace = false) {
    if ($replace || !array_key_exists('csrf', $_SESSION)) {
        $_SESSION['csrf'] = random_hash();
    }
}

function format_size($b) {
    if ($b < 1024) return round($b,2) . 'B';
    $b /= 1024.0;
    if ($b < 1024) return round($b,2) . 'KiB';
    $b /= 1024.0;
    if ($b < 1024) return round($b,2) . 'MiB';
    $b /= 1024.0;
    if ($b < 1024) return round($b,2) . 'GiB';
    $b /= 1024.0;
    return round($b,2) . 'TiB';
}

function site_header() {
    global $CONFIG;
    printf('<!DOCTYPE html>');
    printf('<html>');
    printf('<head>');
    printf('<meta name="viewport" content="width=device-width, initial-scale=1">');
    printf('<meta name="format-detection" content="telephone=no">');
    printf('<link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">');
    printf('<link rel="icon" href="img/favicon.ico" type="image/x-icon">');
	printf('<link rel="stylesheet" href="css/style.css">');
    printf('<script src="js/nav.js"></script>');
    printf('<title>%s</title>', html_escape($CONFIG['site_title']));
    printf('</head>');
    printf('<body>');

    if (array_key_exists('user', $_SESSION)) {
		printf('<nav><ul class="nav" id="nav">');
        printf('<li class="right"><a>Welcome, %s</a></li>', html_escape($_SESSION['user']['username']));
        printf('<li><a href="index.php">Home</a></li>');
        printf('<li><a href="upload.php">Upload</a></li>');
        printf('<li><a href="invitations.php">Invitations</a></li>');
        printf('<li><a href="logout.php">Logout</a></li>');
        printf('<li class="icon"><a href="javascript:void(0);"  onclick="menuFunction()">+</a></li>');
		printf('</ul></nav>');
    }
}

function site_footer() {
    printf('<footer>&copy;%s DevHackers Team</footer>', date('Y'));
    printf('</body>');
}


// session setup
session_start();
gen_csrf();

