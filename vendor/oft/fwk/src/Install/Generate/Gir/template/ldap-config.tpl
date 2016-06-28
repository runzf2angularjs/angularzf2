<?php
echo "<?php\n";
?>

return array(
    'active' => <?= $active ? 'true' : 'false' ?>,
    <?php
    echo "'ldap' => array(";
        
        if ($host !== null) {
            echo "\n" . "        'host' => '$host',";
        }
        if ($username !== null) {
            echo "\n" . "        'username' => '$username',";
        }
        if ($password !== null) {
            echo "\n" . "        'password' => '$password',";
        }
        if ($baseDn !== null) {
            echo "\n" . "        'baseDn' => '$baseDn',";
        }
        if ($port !== null) {
            echo "\n" . "        'port' => $port,";
        }
        if ($useSsl !== null) {
            echo "\n" . "        'useSsl' => $useSsl,";
        }
        echo "\n";
        ?>
    )
);