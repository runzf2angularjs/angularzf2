<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Migrations\Migration;
use Oft\Install\Generate\Admin\AdminGenerator;
use Oft\Install\Generate\Db\DbConfigGenerator;
use Oft\Install\Generate\Gir\LdapConfigGenerator;
use Oft\Install\Tools\DbMigrate\Configuration;
use Oft\Install\Tools\MySql\Schema;
use Oft\Gir\Ldap\Connection;
use Oft\Http\Request;
use Oft\Http\Session as OftSession;
use Oft\Module\ModuleManager;
use Oft\Util\Cache;
use Oft\Util\String;
use Oft\Validator\Cuid;
use Oft\Validator\Password;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Zend\Validator\Regex;

// Racine applicative
define('APP_ROOT', __DIR__ . '/..');

// Initialisation, autoloader
require_once APP_ROOT . '/config/bootstrap.php';

/**
 * ACCES A L'IHM
 */
$allowInstallFile = APP_ROOT . '/data/INSTALL';
if (!file_exists($allowInstallFile)) {
    httpError();
}

/**
 * VARIABLES
 */
// Affichage du bouton "Valider"
$afficherValider = true;
// Message d'informations suite aux traitements (encart en tête du contenu)
$messages = array();

/**
 * CONFIGURATION
 */
// Configuration principale
$mainConfig = include APP_ROOT . '/config/app.php';
// Configuration de BdD
try {
    $dbConfigExists = true;
    $dbConfig = include APP_ROOT . '/application/config/config.db.php';
} catch(ErrorException $e) {
    $dbConfigExists = false;
    $dbConfig = array(
        'driver' => 'pdo_mysql',
        'dbname' => 'mingo',
        'user' => 'root',
        'password' => '',
        'host' => '127.0.0.1',
        'port' => 3306,
        'charset' => 'utf8',
        'driverOptions' => null,
    );
}
// Configuration du GIR
try {
    $girConfigExists = true;
    $girConfig = include APP_ROOT . '/application/config/config.gir.php';
} catch(ErrorException $e) {
    $girConfigExists = false;
    $girConfig = array(
        'active' => false,
        'ldap' => array(
            'useSsl' => false,
            'baseDn' => "ou=people,dc=intrannuaire,dc=orange,dc=com",
            'host' => "ldap-preprod.com.ftgroup",
            'port' => 30002,
            'username' => 'uid=[IDENT],ou=accounts,dc=intrannuaire,dc=orange,dc=com',
            'password' => '[PASSWORD]',
        )
    );
}

/**
 * HTTP
 */
// Session
$sessionStorage = new NativeSessionStorage(array('name' => 'SID'), new NativeSessionHandler());
$symfonySession = new Session($sessionStorage);
$oftSession = new OftSession($symfonySession);
$oftSession->start();
$session = $oftSession->getContainer('Oft\Install');

// Request
$sfRequest = new SymfonyRequest($_GET, array_merge($_POST, $_FILES), array(), $_COOKIE, array(), $_SERVER);
$sfRequest->setSession($symfonySession);
$request = new Request($sfRequest);

/**
 * MODULES
 */
$moduleManager = new ModuleManager();
$moduleManager->addModule($mainConfig['defaultModule'], true);
$moduleManager->addModules($mainConfig['modules']);

// Flag : données postées et validées
$isValider = $request->isPost() && $request->getFromPost('submit', null) == 'Valider';

// Etapes de l'IHM
$steps = array(
    'AUTHENTICATE',
    'SQL_CREATE',
    'ADMIN_USER',
    'SQL_LOAD',
    'GIR',
    'CHANGE_AUTH_KEY',
    'CLEAN_CACHE',
    'END',
);

// Etapes de l'IHM qui n'apparaissent pas dans le menu
$nomenuLinks = array(
    'AUTHENTICATE',
);

// Déconnexion
$disconnect = $request->getFromQuery('deconnexion', false);
if ($disconnect) {
    $oftSession->dropContainer('Oft\Install');
    redirect();
}

// Initialisation
if (!isset($session->step) || !in_array($session->step, $steps) || !isset($session->auth)) {
    $session->step = 'AUTHENTICATE';
    $session->auth = false;
}
if ($session->auth && in_array($request->getFromQuery('step', $session->step), $steps)) {
    $session->step = $request->getFromQuery('step', $session->step);
}

// Etape précédente
$precStep = '';
$nextStep = '';
foreach ($steps as $i => $aStep) {
    if ($aStep == $session->step) {
        if ($i > 1) {
            $precStep = $steps[$i - 1];
        }
        if (isset($steps[$i + 1])) {
            $nextStep = $steps[$i + 1];
        }
        break;
    }
}

// Contrainte de saisie du mot de passe dans le fichier data/INSTALL
$allowInstallFileContent = trim(file_get_contents($allowInstallFile));
if (empty($allowInstallFileContent)) {
    // Contenu du fichier non défini
    $session->step = 'CHANGE_AUTH_KEY';
    $messages[] = "La clef d'installation n'est pas définie dans le fichier 'INSTALL'.";
    $messages[] = "Merci de la définir afin de sécuriser votre installation.";
    $session->auth = true;
    $nextStep = "SQL_CREATE";
}

// Test mod_rewrite
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (!in_array('mod_rewrite', $modules)) {
        $messages[] = "ATTENTION : Le module Apache 'mod_rewrite' n'est pas activé. Il est conseillé de l'activer.";
    }
}

// Authentification
if ($session->step == 'AUTHENTICATE') {
    if ($request->isPost()) {
        $postAuthKey = $request->getFromPost('AUTHENTICATE', null);
        if ($postAuthKey !== $allowInstallFileContent) {
            $messages[] = "La clef d'installation est incorrecte";
        } else {
            $session->auth = true;

            $messages[] = "Validation réalisée";
            redirect($nextStep);
        }
    }
} elseif ($session->step == 'CHANGE_AUTH_KEY') {
    if ($request->isPost()) {
        $newAllowInstallFileContent = $request->getFromPost('CHANGE_AUTH_KEY', null);

        $validatorRegex = new Regex('/[a-zA-Z]+[0-9]+/');
        $validatorPassword = new Password('password', 'password_repeat', 8);
        if ($validatorPassword->isValid($newAllowInstallFileContent, array('password' => $newAllowInstallFileContent, 'password_repeat' => $newAllowInstallFileContent))
            && $validatorRegex->isValid($newAllowInstallFileContent)) {
            $allowInstallFileContent = $newAllowInstallFileContent;
            file_put_contents($allowInstallFile, $newAllowInstallFileContent);
            $messages[] = "La clef a été modifiée";
        } else {
            $messages[] = "ERROR : La clef doit faire minimum 8 caractères alphanumériques avec minimum une lettre et un chiffre";
        }

        if (!$isValider) {
            redirect($nextStep);
        }
    }
} else if ($session->step == 'SQL_CREATE') {

    // Modification des paramètres
    if ($request->isPost()) {

        $changed = false;
        $valid = true;

        foreach ($dbConfig as $key => $value) {
            $postValue = $request->getFromPost($key, null);
            if ($postValue !== null && $postValue !== (string)$dbConfig[$key]) {
                $dbConfig[$key] = $request->getFromPost($key);
                $changed = true;
            }
        }

        // Création du schéma
        if ($request->getFromPost('create_schema') === '1') {
            $cnxUsername = $request->getFromPost('create_schema_user');
            $cnxPassword = $request->getFromPost('create_schema_pwd');

            try {
                $createMessages = Schema::create($dbConfig, $cnxUsername, $cnxPassword);
                $messages = array_merge($messages, $createMessages);
            } catch (Exception $e) {
                $messages[] = "La création du schéma a échouée";
            }
        }

        try {
            // dbname vide autorisé pour ouvrir une connexion
            // mais pas satisfaisant pour le fonctionnement de l'OFT
            if (empty($dbConfig['dbname'])) {
                throw new InvalidArgumentException("Le nom de la base de données ne peut être vide");
            }

            // Test de la connection
            $connection = DriverManager::getConnection($dbConfig);
            $connection->connect();
        } catch (Exception $e) {
            $messages[] = 'ERREUR : Connexion invalide';
            $messages[] = 'Exception "' . get_class($e) . '" : ' . $e->getMessage();
            $valid = false;
        }

        // Création d'un fichier de configuration que si les valeurs sont différentes et valides
        // ou si le fichier n'existe pas encore
        if (!$dbConfigExists || ($changed && $valid)) {
            $generator = new DbConfigGenerator($moduleManager);

            $generator->host = $dbConfig['host'];
            $generator->dbname = $dbConfig['dbname'];
            $generator->user = $dbConfig['user'];
            $generator->password = $dbConfig['password'];

            $generator->port = $dbConfig['port'];
            $generator->charset = $dbConfig['charset'];
            $generator->driver = $dbConfig['driver'];

            if (isset($dbConfig['unix_socket'])) {
                $generator->unixsocket = $dbConfig['unix_socket'];
            }

            if (isset($dbConfig['driverOptions'])) {
                $generator->driverOptions = $dbConfig['driverOptions'];
            }

            $generator->generate();
            $generator->save();

            $messages[] = "Le fichier de configuration a été généré";
        }

        // Valider
        if ($valid) {
            if ($isValider) {
                $messages[] = "Configuration valide et enregistrée";
            } else {
                redirect($nextStep);
            }
        }
    }
} elseif ($session->step == 'ADMIN_USER') {
    $showForm = true;

    try {
        $db = DriverManager::getConnection($dbConfig);
        $db->connect();

        $configurationMigrations = new Configuration($db);
        $configurationMigrations->setMigrationsTableName('oft_migrations');

        $defaultPath = $defaultNamespace = null;

        foreach ($moduleManager->getModules() as $moduleName => $module) {
            $path = $module->getDir('sql');

            $namespace = $moduleManager->getModuleNamespace($moduleName);
            $namespace .= '\Sql';

            if ($moduleName === $moduleManager->getDefault()) {
                $defaultPath = $path;
                $defaultNamespace = $namespace;
            }

            $configurationMigrations->setMigrationsNamespace($namespace);
            $configurationMigrations->registerMigrationsFromDirectory($path);
        }

        $configurationMigrations->setMigrationsNamespace($defaultNamespace);
        $configurationMigrations->setMigrationsDirectory($defaultPath);

        // Gestion de la suppression
        $deleteCuid = $request->getFromQuery('delete', null);
        if ($deleteCuid !== null) {
            $regex = "#Version_" . String::stringToValidClassName($deleteCuid) . "_[0-9]{14}.php$#";

            foreach ($moduleManager->getModules() as $module) {
                $search = glob($module->getDir('sql') . '/Version*.php');
                if (!$search) {
                    continue;
                }

                foreach ($search as $filename) {
                    if (preg_match($regex, $filename)) {
                        if (in_array(substr($filename, -18, -4), $configurationMigrations->getMigratedVersions())) {
                            $messages[] = 'ERROR : Le fichier ' . $filename . ' ne peut être supprimé car il a déjà été migré.';
                            $messages[] ='Veuillez revenir à une version antérieure à \''.substr($filename, -18, -4).'\'';
                        } else {
                            if (@unlink($filename) === false) {
                                $messages[] = 'ERROR : Le fichier ' . $filename . ' n\'a pas pu être supprimé';
                            } else {
                                $messages[] = "Fichier de migration de l'utilisateur '" . $deleteCuid . "' supprimé";
                            }
                        }
                    }
                }
            }
        }

        // Recupération des cuids existants
        $sqlFileExists = false;

        $regex = "#Version_[\w]{1,}_[0-9]{14}.php$#";
        $cuids = array();

        foreach ($moduleManager->getModules() as $module) {
            $search = glob($module->getDir('sql') . '/Version*.php');
            if (!$search) {
                continue;
            }

            foreach ($search as $filename) {
                if (preg_match($regex, $filename)) {
                    $cuidTmp = explode('Version_', substr($filename, 0, -19));
                    $cuids[] = String::reverseStringToValidClassName($cuidTmp[1]);

                    $sqlFileExists = true;
                }
            }
        }

        // Gestion de l'ajout d'un utilisateur
        $cuid = '';
        if ($request->isPost()) {

            if (!$isValider) {
                redirect($nextStep);
            }

            $cuid = $request->getFromPost('cuid', '');

            $validatorCuid = new Cuid();
            if (in_array($cuid, $cuids)) {
                $messages[] = "ERROR : Cet utilisateur existe déjà";
            } elseif ($validatorCuid->isValid($cuid)) {
                $password = $request->getFromPost('password', '');
                $passwordRepeat = $request->getFromPost('password_repeat', '');

                $validatorPassword = new Password('password', 'password_repeat', 8);
                if ($validatorPassword->isValid($password, array('password' => $password, 'password_repeat' => $passwordRepeat))) {
                    try {
                        $generator = new AdminGenerator($moduleManager);

                        $generator->username = $cuid;
                        $generator->password = $password;

                        $files = $generator->generate();
                        $generator->save($files);

                        redirect($isValider ? null : $nextStep);
                    } catch (Exception $e) {
                        $messages[] = "ERROR : " . $e->getMessage();
                    }
                } else {
                    $validatorMessages = $validatorPassword->getMessages();
                    $messages[] = "ERROR : " . array_pop($validatorMessages);
                }
            } else {
                $validatorMessages = $validatorCuid->getMessages();
                $messages[] = "ERROR : " . array_pop($validatorMessages);
            }
        }

        if (!$sqlFileExists) {
            $messages[] = "Aucun administrateur n'a été configuré pour le chargement de la base";
        }
    } catch (Exception $e) {
        $sqlFileExists = $showForm = false;
        $messages[] = "Cette action est impossible pour le moment car la base de données n'est pas correctement configurée";
    }
} elseif ($session->step == 'SQL_LOAD') {
    // Execution réalisée
    $doExecSql = $request->getFromPost('doExecSql', false);
    $versionMigration = $request->getFromPost('versionMigration', null);

    // Fichier réellement exécutés
    $sqlFiles = array();
    $versionFiles = array();
    $versionsDetails = array();
    $currentVersion = null;

    try {
        $db = DriverManager::getConnection($dbConfig);
        $db->connect();

        $configurationMigrations = new Configuration($db);
        $configurationMigrations->setMigrationsTableName('oft_migrations');

        $defaultPath = $defaultNamespace = null;

        foreach ($moduleManager->getModules() as $moduleName => $module) {
            $path = $module->getDir('sql');

            $namespace = $moduleManager->getModuleNamespace($moduleName);
            $namespace .= '\Sql';

            if ($moduleName === $moduleManager->getDefault()) {
                $defaultPath = $path;
                $defaultNamespace = $namespace;
            }

            $configurationMigrations->setMigrationsNamespace($namespace);
            $configurationMigrations->registerMigrationsFromDirectory($path);
        }

        $configurationMigrations->setMigrationsNamespace($defaultNamespace);
        $configurationMigrations->setMigrationsDirectory($defaultPath);

        // Récupèration des fichiers a exécuter
        foreach ($moduleManager->getModules() as $module) {
            $migrations = glob($module->getDir('sql') . '/Version*.php');
            if (!$migrations) {
                continue;
            }

            foreach ($migrations as $filename) {
                $version = substr(substr($filename, -18), 0, -4);
                $versionFiles[$version] = date_format(date_create_from_format('YmdHis', $version), 'd/m/Y H:i:s');
                $sqlFiles[$version] = $filename;
            }
        }

        ksort($sqlFiles);
        ksort($versionFiles);

        $versionFiles[max(array_keys($versionFiles))] = 'la plus récente';

        if (!count($sqlFiles)) {
            $messages[] = "Pas de fichiers de migration\n";
        }

        if ($request->isPost() && $doExecSql == 'yes') {
            try {
                $migration = new Migration($configurationMigrations);

                $migration->migrate($versionMigration);

                $messages[] = "OK : Version '$versionMigration' exécutée";
            } catch (Exception $e) {
                $messages[] = "ERROR : " . $e->getMessage();
            }
        }

        foreach ($configurationMigrations->getAvailableVersions() as $version) {
            $versionsDetails[$version] = array(
                'version' => $version,
                'date' => date_format(date_create_from_format('YmdHis', $version), 'd/m/Y H:i:s'),
                'migrated' => in_array($version, $configurationMigrations->getMigratedVersions()),
            );
        }

        ksort($versionsDetails);

        $currentVersion = $configurationMigrations->getCurrentVersion();

    } catch (Exception $e) {
        $sqlFileExists = $showForm = false;
        $messages[] = "Cette action est impossible pour le moment car la base de données n'est pas correctement configurée";
    }

    if ($request->isPost()) {
        if (!$isValider) {
            redirect($nextStep);
        }
    }
} elseif ($session->step == 'GIR') {

    // Configuration LDAP
    $ldapConfig = $girConfig['ldap'];

    // Fxé activée
    $girActive = (isset($girConfig['active']) && $girConfig['active'] == true) ? true : false;

    // Modification des paramètres
    if ($request->isPost()) {

        if (!$isValider) {
            redirect($nextStep);
        }

        $changed = false;
        $valid = true;

        // Fxé activée
        $girActive = ($request->getFromPost('gir_active') === '1') ? true : false;

        // Keys
        foreach ($ldapConfig as $key => $value) {
            $postValue = $request->getFromPost($key, null);
            if ($key == 'useSsl') {
                $postValue = $postValue == '1' ? 1 : 0;
            }
            if ($postValue != (string)$ldapConfig[$key]) {
                $ldapConfig[$key] = $postValue;
                $changed = true;
            }
        }

        try {
            // Test de la connection
            $ldap = new Connection();
            $ldap->setOptions($ldapConfig);
            $ldap->bind();
        } catch (Exception $e) {
            $messages[] = 'ERREUR : '. $e->getMessage();
            $valid = false;

            // Le fichier de configuration sera quand même créé car attendu par l'application
            // On désactive la fonctionnalité tant que la connexion est KO
            $girActive = false;
        }

        // Création d'un fichier de configuration que si les valeurs sont différentes
        // ou si le fichier n'existe pas encore
        if (!$girConfigExists || ($changed && $girConfigExists && $valid)) {
            $generator = new LdapConfigGenerator($moduleManager);

            $generator->active = $girActive;
            $generator->useSsl = $ldapConfig['useSsl'] == true ? 1 : 0;
            $generator->baseDn = $ldapConfig['baseDn'];
            $generator->host = $ldapConfig['host'];
            $generator->username = $ldapConfig['username'];
            $generator->password = $ldapConfig['password'];
            $generator->port = (int)$ldapConfig['port'];

            $generator->generate();
            $generator->save();
        }

        // Valider
        if ($isValider) {
            if ($valid) {
                $messages[] = "Configuration valide, le fichier de configuration a été créé";
            } else {
                $messages[] = 'La connexion n\'est pas valide';
            }
        } else {
            redirect($nextStep);
        }
    }

} elseif ($session->step == 'CLEAN_CACHE') {
        // Inclusion des resources
    if ($request->isPost()) {
        $actionCache = $request->getFromPost('action', null);

        switch ($actionCache) {
            case 'emptyCache':
                try {
                    Cache::clearCache();

                    $messages[] = 'OK : Cache vidé';
                } catch (Exception $e) {
                    $messages[] = 'ERROR : ' . $e->getMessage();
                }

                break;
            default :
                if (!$isValider) {
                    redirect($nextStep);
                }

                break;
        }
    }
} elseif ($session->step == 'END') {
    $actionFile = $request->getFromPost('action', null);
    if ($request->isPost()) {
        switch ($actionFile) {
            case 'supprimerFichier':
                session_destroy();
                unlink($allowInstallFile);
                $messages[] = "OK : Fichier supprimé.";
                break;
        }
    }
}

header('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <title>Installation Oft_Framework - <?php echo getStepTitle($session->step); ?></title>

        <style type='text/css'>
            html {
                width: 100%;
                height: 100%;
                background: #f9f9f9;
            }

            body {
                width: 980px;
                margin: 10px auto;

                font-family: Arial, sans-serif;
                font-size: 0.9em;
                color: #3d3d3d;

                background: #fff;
                border: 1px solid #ccc;
            }

            a { text-decoration: none; color: #333; }
            a:hover { text-decoration: underline; color: #ff5500; }

            input { margin-bottom: 3px; }

            ul, ul li { padding: 0; margin: 0 15px; }

            h1, h2 {
                text-align: center;
                margin: 4px auto;
            }

            h1 {
                font: 2.2em "Helvetica 45 Light",Arial,sans-serif;
            }

            h2 {
                font: 2em "Helvetica 45 Light",Arial,sans-serif;
                color: #ff5500;
            }

            #menuBox {
                float: left;
                width: 220px;
                margin: 5px;
            }
            .menu ol li {
                padding: 1px 0;
            }

            #contentTable {
                margin: 0 auto;
                width: 740px;
                height: 400px;
            }
            #contentTable td, #contentTable th {
                text-align: left;
                vertical-align: top;
                padding: 4px 8px;
            }
            #contentTable th.label {
                text-align: right;
                font-weight: normal;
            }
            #contentTable td.buttons {
                border-top: 1px solid #ccc;
                padding: 8px 10px;
                text-align: right;
            }
            #contentTable td.messages {
                color: #ff5500;
                border: 1px solid #ff5500;
            }
            #contentTable td.messages ul {
                list-style: none;
            }
            .table tr.pair {
                background: #f2f2f2;
            }
            .table td { v-align: bottom; }
            .suggestedAddons {
                padding: 2px 4px;
                margin: 4px;

                float: left;
                list-style: none;

                border: 1px solid #ccc;
                cursor: pointer;
            }
        </style>

        <script type="text/javascript">
            /* Fonction lancée à l'évènement onload sur la page */
            function onload()
            {
                // Vérification de la clef de sécurité si le champ est présent dans la page
                var authInput = document.getElementById('CHANGE_AUTH_KEY');
                if (authInput) {
                    // Lancement de la fonction au focus et au changement de valeur
                    authInput.onkeyup = checkAuthKey;
                    authInput.onfocus = checkAuthKey;
                }
            }

            /* Fonction de vérification de la clef de sécurité */
            function checkAuthKey()
            {
                console.log(document.getElementById('CHANGE_AUTH_KEY'));

                var authInput = document.getElementById('CHANGE_AUTH_KEY');
                var msgAll = ['checkKeyResult-long', 'checkKeyResult-alpha', 'checkKeyResult-num', 'checkKeyResult-alnumonly'];

                var msgDisplay = [];
                var checkKeyArea = document.getElementById('checkKey');

                // Test de la longueur
                if (authInput.value.length < 8) {
                    msgDisplay.push('checkKeyResult-long');
                }

                // Test de la présence d'au moins 1 caractères alpha
                if (!/[a-zA-Z]+/.test(authInput.value)) {
                    msgDisplay.push('checkKeyResult-alpha');
                }

                // Test de la présence d'au moins 1 caractères numérique
                if (!/[0-9]+/.test(authInput.value)) {
                    msgDisplay.push('checkKeyResult-num');
                }

                // Test de l'absence de caractères autres qu'alphanumériques
                if (!/^[a-zA-Z0-9]+$/.test(authInput.value)) {
                    msgDisplay.push('checkKeyResult-alnumonly');
                }

                // Init
                checkKeyArea.style.display = "inline-block";
                for (var i = 0; i < msgAll.length; i++) {
                    document.getElementById(msgAll[i]).style.display = "none";
                }

                if (msgDisplay.length > 0) {
                    document.getElementsByClassName('checkKeyResultKo')[0].style.display = "inline-block";
                    document.getElementsByClassName('checkKeyResultOk')[0].style.display = "none";

                    for (var i = 0; i < msgDisplay.length; i++) {
                        document.getElementById(msgDisplay[i]).style.display = "inline-block";
                    }
                } else {
                    document.getElementsByClassName('checkKeyResultKo')[0].style.display = "none";
                    document.getElementsByClassName('checkKeyResultOk')[0].style.display = "inline-block";
                }
            }
        </script>
    </head>
    <body onload="onload()">
        <?php if (isset($session->auth) && $session->auth) : ?>
            <div id="menuBox">
                <div class='menu'>
            <?php
            $stopLink = false;
            echo '<ol>';
            foreach ($steps as $linkStep) {
                if (in_array($linkStep, $nomenuLinks)) {
                    continue;
                }
                echo "<li>";
                if ($linkStep == $session->step) {
                    echo '<b>', getStepTitle($linkStep), "</b>";
                } elseif (!empty($_SESSION['forceStep'])) {
                    echo getStepTitle($linkStep);
                } else {
                    echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?step=', $linkStep, '">', getStepTitle($linkStep), '</a>';
                }
                echo "</li>\n";
            }
            echo '</ol>';

            echo "<ul>";
            echo '<li><a href=".">Accéder à l\'application</a></li>';
            echo '<li><a href="' . $_SERVER['SCRIPT_NAME'] . '?deconnexion=1">Déconnexion</a><br /></li>';
            echo '</ul><br />';
            echo "<ul>";
            echo '<li><a href="http://oft2-ccphp.kermit.rd.francetelecom.fr" target="_blank">Documentation OFT (Intranet)</a></li>';
            echo '</ul>';
            ?>
                </div>
            </div>
        <?php endif; ?>
        <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="POST" name="formInstall" id="formInstall">

            <input type="hidden" name="step" value="<?php echo $session->step ?>">

            <table id='contentTable'>
                <tr>
                    <th colspan="2">
                        <h1>Installation de l'OFT</h1>
                        <h2><?php echo getStepTitle($session->step); ?></h2>
                    </th>
                </tr>
                <tr>
                    <td colspan="2" class="help">
                        <?php echo getStepHelp($session->step); ?>
                    </td>
                </tr>
                        <?php if (count($messages)) : ?>
                    <tr>
                        <td colspan="2" class="messages">
                    <?php echo htmlList($messages); ?>
                        </td>
                    </tr>
                        <?php endif; ?>


                <?php if ($session->step == 'AUTHENTICATE') : ?>
                    <tr>
                        <th class="label"><label for="AUTHENTICATE">Clef d'installation :</label></th>
                        <td><?php echo formText('AUTHENTICATE', isset($newAllowInstallFileContent) ? $newAllowInstallFileContent : '', 'password'); ?></td>
                    </tr>
                    <tr><td colspan="2"><br/></td></tr>


                <?php elseif ($session->step == 'CHANGE_AUTH_KEY') : ?>
                <tr>
                    <th class="label"><label for="CHANGE_AUTH_KEY">Changer la clef d'installation :</label></th>
                    <td height="150" width="430">
                        <?php echo formText('CHANGE_AUTH_KEY', isset($newAllowInstallFileContent) ? $newAllowInstallFileContent : '', 'text'); ?>
                        <div id="checkKey" style="display: none;">
                            <div style="margin: 5px 0;">
                                Robustesse de la clef :
                                <span class="checkKeyResultKo" style="color: #a10d22;">insuffisante</span>
                                <span class="checkKeyResultOk" style="color: #318b04;">acceptable</span>
                            </div>
                            <div id="checkKeyResult-long" style="display: none;">&times; La clef est trop courte</div>
                            <div id="checkKeyResult-alpha" style="display: none;">&times; La clef devrait contenir au moins 1 caractère alphabétique</div>
                            <div id="checkKeyResult-num" style="display: none;">&times; La clef devrait contenir au moins 1 caractère numérique</div>
                            <div id="checkKeyResult-alnumonly" style="display: none;">&times; La clef ne doit contenir que des caractères alphanumérique</div>
                        </div>
                    </td>
                </tr>
                <tr><td colspan="2"><br/></td></tr>

                <?php elseif ($session->step === 'SQL_CREATE') : ?>
                    <?php
                        foreach ($dbConfig as $key => $value) {
                            if (is_array($value)) {
                                continue; // Les éléments de configuration qui sont des tableaux sont ignorés dans cette IHM
                            }
                            echo '<tr><th class="label"><label for="' . $key . '">', $key, ' : </label></th>', '<td>',
                            formText($key, $value), '</td></tr>';
                        }
                    ?>
                    <tr>
                        <td colspan='2'><hr></td>
                    </tr>
                    <tr>
                        <th class="label"><label>Créer le schéma et l'utilisateur :</label></th>
                        <td><?= formSelect('create_schema', array(0 => 'Non', 1 => 'Oui'), 0) ?><br><i>Si "oui", veuillez saisir des identifiants administrateur ci-dessous</i></td>
                    </tr>
                    <tr>
                        <th class="label"><label>Nom d'utilisateur :</label></th>
                        <td><?= formText('create_schema_user') ?></td>
                    </tr>
                    <tr>
                        <th class="label"><label>Mot de passe :</label></th>
                        <td><?= formText('create_schema_pwd') ?></td>
                    </tr>
                <tr><td colspan="2"><br/></td></tr>


                <?php elseif ($session->step == 'ADMIN_USER') : ?>
                    <?php if ($sqlFileExists) : ?>
                        <tr>
                            <td colspan="2">
                                Administrateurs actuellement définis :
                                <table class='table'>
                                    <thead>
                                    <th style="width: 250px;">Nom d'utilisateur</th>
                                    <th>Supprimer</th>
                                    </thead>
                                    <tbody>
                        <?php
                        $i = 0;
                        foreach ($cuids as $key => $cuidValue) {
                            $i++;
                            $pair = ($i % 2 == 0) ? ' class="pair" ' : '';

                            echo '
                                <tr ' . $pair . '>
                                    <td>' . $cuidValue . '</td>
                                    <td>
                                            <input type="button" onclick="document.location=\'' . $_SERVER['SCRIPT_NAME'] . '?delete=' . $cuidValue . '\';" value="Supprimer" />
                                    </td>
                                </tr>';
                        }
                        ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($showForm) : ?>
                    <tr>
                        <th>Ajouter un administrateur</th>
                    </tr>
                    <tr>
                        <th class="label"><label for="cuid">Cuid : </label></th>
                        <td><?php echo formText('cuid', $cuid); ?></td>
                    </tr>
                    <tr>
                        <th class="label"><label for="password">Mot de passe : </label></th>
                        <td><input type="password" name="password" id="password"></td>
                    </tr>
                    <tr>
                        <th class="label"><label for="password_repeat">Mot de passe (encore) : </label></th>
                        <td><input type="password" name="password_repeat" id="password_repeat"></td>
                    </tr>
                    <?php endif; ?>
                <tr><td colspan="2"><br/></td></tr>


                <?php elseif ($session->step == 'SQL_LOAD') : ?>
                    <?php if (!empty($sqlFiles)) : ?>

                    <tr>
                        <th colspan="2">Fichiers de migrations :</th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?php echo htmlList($sqlFiles); ?>
                            <br />
                        </td>
                    </tr>
                    <?php if(! empty($sqlFiles)) : ?>
                    <tr>
                        <td>
                            <table style="border: 1px solid #ccc" width="100%">
                                <tr>
                                    <th>Version</th>
                                    <th>Installée</th>
                                </tr>

                                <?php foreach($versionsDetails as $versionDetails) : ?>
                                <tr>
                                    <td><?= $versionDetails['date'].'&nbsp;(' . $versionDetails['version'] ?>)</td>
                                    <td><?= ($versionDetails['migrated']) ? '<b>OUI</b>' : 'NON' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2">Appliquer une version :</th>
                    </tr>
                    <tr>
                        <td>
                            <ul>
                                <li>
                                    <label for="versionMigration">Je veux migrer vers la version </label><?php echo formSelect('versionMigration', $versionFiles, ($currentVersion == 0) ? max(array_keys($versionFiles)) : $currentVersion) ?>
                                    <label for="doExecSql">et je confirme l'exécution : </label><?php echo formSelect('doExecSql', array('no' => 'Non', 'yes' => 'Oui'), 'no') ?>
                                </li>
                            </ul>
                        </td>
                    </tr>
                    <?php endif; ?>
                <?php endif; ?>
                <tr><td colspan="2"><br/></td></tr>

                <?php elseif ($session->step === 'GIR') : ?>
                    <tr>
                        <th class="label"><label for="gir_active">activer la fonctionnalité : </label></th>
                        <td><?= formSelect('gir_active', array('1' => 'Oui', '0' => 'Non'), $girActive ? '1' : '0') ?></td>
                    </tr>
                    <?php
                    foreach ($ldapConfig as $key => $value) {
                        echo '<tr><th class="label"><label for="' . $key . '">', $key, ' : </label></th>', '<td>';

                        if ($key == 'useSsl') {
                            $valueSsl = ($value) ? '1' : '0';
                            echo formSelect($key, array('1' => 'Oui', '0' => 'Non'), $valueSsl), '</td></tr>';
                        } else {
                            echo formText($key, $value), '</td></tr>';
                        }
                    }
                    ?>


                <?php elseif ($session->step == 'CLEAN_CACHE') : ?>
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <button type="submit" name="action" value="emptyCache">Vider le cache</button>
                        </td>
                    </tr>


                <?php elseif ($session->step == 'END') : ?>
                    <tr>
                        <td colspan="2" style="text-align: center">
                            <?php
                                $file = realpath($allowInstallFile);
                                if ($file) :
                            ?>
                            <button type="submit" name="action" value="supprimerFichier">Supprimer le fichier</button>
                            <?php else : ?>
                                <p>
                                    Le fichier d'installation est supprimé,<br /> cette page est maintenant inaccessible.<br />
                                    <br />
                                    <input type="button" onclick="document.location.href='.';" value="Accéder à l'application" />
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endif; ?>
                <tr>
                    <td colspan="2" class="buttons">
                        <?php if ($precStep) : ?>
                            <input type="button" onclick="document.location = '<?php echo $_SERVER['SCRIPT_NAME']; ?>?step=<?php echo $precStep ?>';" value="&lt;&lt; Précédent">
                        <?php endif; ?>
                        <input type="button" onclick="document.location = '<?php echo $_SERVER['SCRIPT_NAME']; ?>';" value="Actualiser">
                        <?php if ($session->step !== 'END' && $afficherValider) : ?>
                            <input type="submit" name="submit" value="Valider">
                        <?php endif; ?>
                        <?php if ($nextStep) : ?>
                            <input type="submit" name="nextStep" value="Etape suivante &gt;&gt;">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>

<?php

/**
 * Récupère le titre de l'étape
 *
 * @return string
 */
function getStepTitle($step)
{
    // Titre de l'étape
    switch ($step) {
        default:
            $title = 'Pas de titre';
            break;
        case 'AUTHENTICATE':
            $title = 'Authentification';
            break;
        case 'CHANGE_AUTH_KEY':
            $title = 'Clef d\'installation';
            break;
        case 'HTACCESS':
            $title = 'Fichier .htaccess';
            break;
        case 'SQL_CREATE':
            $title = 'Définition du schéma';
            break;
        case 'ADMIN_USER':
            $title = 'Administrateurs';
            break;
        case 'SQL_LOAD':
            $title = 'Chargement de la base';
            break;
        case 'ADDONS':
            $title = 'Configuration des addons';
            break;
        case 'GIR':
            $title = 'Configuration GIR';
            break;
        case 'CLEAN_CACHE':
            $title = 'Vider le cache';
            break;
        case 'END':
            $title = 'Désactiver cette interface';
            break;
    }
    return $title;
}

function getStepHelp($step)
{
    $help = '';

    // Titre de l'étape
    switch ($step) {
        default:
            break;
        case 'AUTHENTICATE':
            $help = "<p>Vous devez entrer la clef vous permettant d'accèder à cette interface.</p>";
            break;
        case 'CHANGE_AUTH_KEY':
            $help = "<p>Vous pouvez modifier la valeur de la clef vous permettant d'accèder à cette interface.</p>";
            break;
        case 'HTACCESS':
            $help = "<p>Vous pouvez changer les variables d'environnements propres à votre application. <br>Ces valeurs sont définies dans le fichier .htaccess que vous pouvez aussi mettre à jour manuellement.</p>";
            break;
        case 'SQL_CREATE':
            $help =
                "<p>Cette page vous permet de définir les informations d'accès à la base de données de votre application.</p>" .
                "<p>En environnement de développement, vous pouvez aussi créer votre schéma. Pour cela vous devez indiquez les informations de connexion de l'utilisateur priviliégié de votre base de donnée (généralement 'root'). Si vous choisisez cette option, le schéma et l'utilisateur spécifiés seront créés si ils n'existent pas.</p>";
            break;
        case 'ADMIN_USER':
            $help = "<p>Créez ou supprimez les utilisateurs 'administrateurs' de l'application. Ces opérations créés ou suppriment des fichiers de migration qui seront exécutés à l'étape suivante.</p><p><b>Note : </b>les mots de passe ne sont <b>pas</b> en clair dans les fichiers de migration.</p>";
            break;
        case 'SQL_LOAD':
            $help = "<p>Pour la base de données précédemment configurée, choisissez quelle version du schéma vous souhaitez installer.</p><p>Les fichiers de migration de l'application sont situés dans <i>'" . APP_ROOT . "/application/sql/Version*.php</i>'. Vous pouvez en ajouter afin qu'ils soient pris en compte lors de l'initialisation de votre application.</p><p>Les fichiers de migration chargés d'initialiser les tables du framework sont situés dans les modules de l'OFT.</p><p><b>Attention : </b> choisir une version antérieure à la dernière installée provoquera la désinstallation des versions plus récentes (effacement des données et tables concernées).</p>";
            break;
        case 'GIR':
            $help = "<p>Si vous avez un contrat d'interface avec l'annuaire interne groupe, vous pouvez entrer ici vos informations de connexion.</p>";
            break;
        case 'ADDONS':
            $help = "
            	<p>Cette page vous permet d'installer, désactiver et actualiser les addons de l'application</p>
            	<ul>
            		<li>L'emplacement suggéré de stockage des addons est :<br />" . APP_ROOT . DS . 'vendors' . "</li>
            		<li>L'installation peut entraîner l'ajout de fichiers statiques dans le répertoire /public/media/[addon-name] et la création d'une ou plusieurs tables</li>
            		<li>L'actualisation met à jour les fichiers statiques dans l'application à partir de ceux présent dans /[addon-path]/media/</li>
            		<li>La désactivation rend l'addon inaccessible et inopérant sur l'application mais n'affecte pas les données créées pour et par l'addon (scripts de l'addon, fichiers statiques, base de données, etc.)</li>
            	</ul>
            ";
            break;
        case 'CLEAN_CACHE':
            $help = "<p>Vous pouvez supprimer l'ensemble des fichiers de cache de l'application en cliquant sur le bouton.</p>";
            break;
        case 'END':
            $help = "<p>Vous pouvez supprimer le fichier '" . APP_ROOT . "/data/INSTALL' pour ne plus permettre l'accès à cette interface.</p><p><b>Ceci est fortement recommandé en PRODUCTION.</b></p>";
            break;
    }
    return $help;
}

function htmlList($values, $escape = true)
{
    $html = '<ul>';
    foreach ($values as $key => $value) {
        $html .= '<li>';
        if (is_string($value)) {
            if ($escape) {
                $html .= escape($value);
            } else {
                $html .= $value;
            }
        } elseif (is_array($value)) {
            $html .= $key . ' : ';
            $html .= htmlList($value, $escape);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}

function formSelect($name, $values, $selected, $changeId = null)
{
    $onChange = '';
    if ($changeId !== null) {
        $onChange = 'onchange="if (this.value) { document.getElementById(\''
            . $changeId . '\').value=this.value; }" ';
        // Ajout d'une entrée vide
        $values = array('' => '') + $values;
        $selected = 0;
    }

    $html = '<select name="' . escape($name) . '" id="' . escape($name) . '" ' . $onChange . '>';
    foreach ($values as $k => $v) {
        $optionSelected = $selected == $k ? 'selected="selected"' : '';
        $html .= '<option value="'
            . escape($k) . '" '
            . escape($optionSelected) . '>'
            . escape($v) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function formText($name, $value = '', $type = 'text')
{
    return '<input style="width: 300px" type="' . $type . '"
    	name="' . escape($name) . '" id="' . escape($name) . '"
        value="' . escape($value) . '">';
}

function escape($text)
{
    return htmlentities($text, ENT_NOQUOTES, 'UTF-8');
}

function redirect($step = null)
{
    header(
        'Location: '
        . $_SERVER['SCRIPT_NAME']
        . ($step !== null ? '?step=' . $step : '')
    );
    die('redirect');
}

function httpError()
{
    header('HTTP/1.1 404 Not Found', true, 404);
    echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' . "\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<title>404 Not Found</title>\n";
    echo "</head>\n";
    echo "<body>\n";
    echo "<h1>Not Found</h1>\n";
    echo "<p>The requested URL ", $_SERVER['SCRIPT_NAME'], " was not found on this server.</p>\n";
    echo "</body>\n";
    echo "</html>\n";
    die();
}
