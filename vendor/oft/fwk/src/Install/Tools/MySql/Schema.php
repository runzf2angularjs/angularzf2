<?php
/**
 * Copyright (C) 2015 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * If you are Orange employee you shall use this software in accordance with
 * the Orange Source Charter (http://opensource.itn.ftgroup/index.php/Orange_Source).
 */

namespace Oft\Install\Tools\MySql;

use Doctrine\DBAL\DriverManager;

class Schema
{

    /**
     * Création d'un nouveau schéma
     *
     * @param array $dbOptions
     * @param string $username
     * @param string $password
     * @return array
     */
    public static function create($dbOptions, $username, $password)
    {
        // Récupération des paramètres
        $dbHost = $dbOptions['host'];
        $dbName = $dbOptions['dbname'];
        $dbUsername = $dbOptions['user'];
        $dbPassword = $dbOptions['password'];

        // Adaptation des paramètres
        $dbCnxOptions = $dbOptions;
        $dbCnxOptions['dbname'] = '';
        $dbCnxOptions['user'] = $username;
        $dbCnxOptions['password'] = $password;

        // Création de l'adaptateur
        $db = DriverManager::getConnection($dbCnxOptions);

        // Validation de la connexion
        $db->connect();

        $messages = array();
        
        // Vérification de l'existance du schéma
        $queryBuilder = $db->createQueryBuilder();
        $schema = $queryBuilder->select('*')->from('information_schema.schemata')
            ->where('SCHEMA_NAME = :dbName')
            ->setParameter('dbName', $dbName)
            ->execute()
            ->fetchAll();

        if (count($schema)) {
            // Schéma existant
            $messages[] = "Le schéma '$dbName' existe déjà";
        } else {
            // Création du schéma
            $sql = 'CREATE SCHEMA IF NOT EXISTS ' . $db->quoteIdentifier($dbName)
                . ' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
            $db->query($sql);
            $messages[] = "Le schéma '$dbName' a été créé";
        }

        // Vérification de l'utilisateur
        if ($dbUsername == 'root') {
            // Utilisateur root
            $messages[] = "Il n'est pas nécessaire de positionner des droits (utilisateur = root)";
        } else {
            // Vérification de l'existance de l'utilisateur
            $users = $queryBuilder->select('*')->from('mysql.user')
                ->where('User = :user')
                ->setParameter('user', $dbUsername)
                ->execute()
                ->fetchAll();
            if (count($users)) {
                // L'utilisateur existe déjà
                $messages[] = "L'utilisateur '$dbUsername' existe déjà";
            } else {
                // Création de l'utilisateur
                $identifiedBy = (!empty($dbPassword)) ? ' IDENTIFIED BY ' . $db->quote($dbPassword) : '';
                $sql = 'CREATE USER ' . $db->quoteIdentifier($dbUsername) . '@' . $db->quote($dbHost) . $identifiedBy . ';';
                $db->query($sql);
                $messages[] = "L'utilisateur '$dbUsername' a été créé";
            }
            
            // Création des droits basiques
            $sql = 'GRANT USAGE ON *.*'
                . ' TO ' . $db->quoteIdentifier($dbUsername) . '@' . $db->quote($dbHost)
                . ' WITH MAX_USER_CONNECTIONS 5';
            $db->query($sql);
            $messages[] = "Droits basiques de l'utilisateur créés";

            // Création des droits sur le schéma
            $sql = 'GRANT'
                . ' SELECT,'
                . ' INSERT,'
                . ' UPDATE,'
                . ' DELETE,'
                . ' CREATE,'
                . ' DROP,'
                . ' REFERENCES,'
                . ' INDEX,'
                . ' ALTER,'
                . ' CREATE TEMPORARY TABLES,'
                . ' LOCK TABLES,'
                . ' CREATE VIEW,'
                . ' SHOW VIEW'
                . ' ON ' . $db->quoteIdentifier($dbName) . '.*'
                . ' TO ' . $db->quoteIdentifier($dbUsername) . '@' . $db->quote($dbHost)
                . ' WITH MAX_USER_CONNECTIONS 5';
            $db->query($sql);
            $messages[] = "Droits de l'utilisateur sur le schema '$dbName' créés";
        }

        return $messages;
    }

    /**
     * Suppression d'un schéma existant
     *
     * @param array $dbOptions
     * @param string $username
     * @param string $password
     * @return array
     */
    public static function drop($dbOptions, $username, $password)
    {
        // Récupération des paramètres
        $dbHost = $dbOptions['host'];
        $dbName = $dbOptions['dbname'];
        $dbUsername = $dbOptions['user'];

        // Adaptation des paramètres
        $dbCnxOptions = $dbOptions;
        $dbCnxOptions['dbname'] = '';
        $dbCnxOptions['user'] = $username;
        $dbCnxOptions['password'] = $password;

        // Création de l'adaptateur
        $db = DriverManager::getConnection($dbCnxOptions);

        // Validation de la connexion
        $db->connect();

        $messages = array();

        // Vérification de l'existance du schéma
        $queryBuilder = $db->createQueryBuilder();
        $schema = $queryBuilder->select('*')->from('information_schema.schemata')
            ->where('SCHEMA_NAME = :dbName')
            ->setParameter('dbName', $dbName)
            ->execute()
            ->fetchAll();

        if (count($schema)) {
            // Schéma existant : suppression
            $sql = 'DROP SCHEMA IF EXISTS ' . $db->quoteIdentifier($dbName) . ';';
            $db->query($sql);
            $messages[] = "Le schéma '$dbName' a été supprimé";
        } else {
            // Schéma inexistant
            $messages[] = "Le schéma '$dbName' n'existe pas";
        }

        // Vérification de l'utilisateur
        if ($dbUsername == 'root') {
            // Utilisateur root
            $messages[] = "Aucune opération sur l'utilisateur (utilisateur = root)";
        } else {
            $users = $queryBuilder->select('*')->from('mysql.user')
                ->where('User = :user')
                ->setParameter('user', $dbUsername)
                ->execute()
                ->fetchAll();
            if (count($users)) {
                // Schéma existant : suppression
                $sql = 'DROP USER ' . $db->quoteIdentifier($dbUsername) . '@' . $db->quote($dbHost) . ';';
                $db->query($sql);
                $messages[] = "L'utilisateur '$dbUsername' a été supprimé";
            } else {
                $messages[] = "L'utilisateur '$dbUsername' n'existe pas";
            }
        }

        return $messages;
    }

}
