<?php
echo "<?php\n";
?>

return array(
    'dbname' => '<?=$dbname?>',
    'user' => '<?=$user?>',
    'password' => '<?=$password?>',
    'host' => '<?=$host?>', <?php
        if ($charset !== null) {
            echo "\n" . "    'charset' => '$charset',";
        }
        if ($driver !== null) {
            echo "\n" . "    'driver' => '$driver',";
        }
        if ($unixsocket !== null) {
            echo "\n" . "    'unix_socket' => '$unixsocket',";
        }
        if ($port !== null) {
            echo "\n" . "    'port' => '$port',";
        }
        if ($driverOptions !== null) {
            echo "\n" . "    'driverOptions' => array(";
            foreach($driverOptions as $key => $value) {
                echo "\n" . "        " . $key . " => " . $value . ",";
            }
            echo "\n" . "    ),";
        }
        echo "\n";
    ?>
);