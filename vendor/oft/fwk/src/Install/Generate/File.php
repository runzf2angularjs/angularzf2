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

namespace Oft\Install\Generate;

use ErrorException;

class File
{

    /**
     * Flag : nouveau fichier
     */
    const CREATE = 'create';

    /**
     * Flag : fichier existant à écraser
     */
    const OVERWRITE = 'overwrite';

    /**
     * Flag : fichier existant, ne sera pas généré
     */
    const SKIP = 'skip';

    /**
     * Chemin d'enregistrement du fichier
     *
     * @var string
     */
    protected $path;

    /**
     * Contenu du fichier
     *
     * @var string
     */
    protected $content;

    /**
     * Opération à effectuer sur le fichier
     *
     * @var string CREATE|OVERWRITE|SKIP
     */
    protected $operation;

    /**
     * Flag : création d'une sauvegarde du fichier
     *
     * @var bool
     */
    protected $doBackup;

    /**
     * @param string $path
     * @param string $content
     * @param bool $doBackup
     * @param bool $overwriteIfExists
     */
    public function __construct($path, $content, $doBackup = true, $overwriteIfExists = true)
    {
        $this->path = strtr($path, '/\\', '//');
        $this->content = $content;
        $this->doBackup = $doBackup;

        if (is_file($path) && file_get_contents($path) === $content) { // Existants et égaux
            $this->operation = self::SKIP;
        } else if (is_file($path) && file_get_contents($path) !== $content) { // Existants & différents
            if ($overwriteIfExists === true) { // Remplacement seulement si flag à true
                $this->operation = self::OVERWRITE;
            } else { // Sinon, aucune action
                $this->operation = self::SKIP;
            }
        } else { // N'existe pas
            $this->operation = self::CREATE;
        }
    }

    /**
     * Sauvegarde du fichier actuel
     *
     * Retourne VRAI si le fichier a été créé
     * Retourne le message d'erreur en cas d'échec
     *
     * @return string|bool
     */
    public function backup()
    {
        if ($this->doBackup === false) {
            return false;
        }

        $path = $this->getBackupPath();

        try {
            file_put_contents($path, file_get_contents($this->path));
        } catch(ErrorException $e) {
            return "Impossible de créer le fichier de sauvegarde '" . $path . "'";
        }

        return true;
    }

    /**
     * Enregistrement du fichier
     *
     * Retourne VRAI si le fichier a été créé
     * Retourne le message d'erreur en cas d'échec
     *
     * @return string|bool
     */
    public function save()
    {
        // Création de l'aborescence
        if ($this->operation === self::CREATE) {
            $dir = dirname($this->path);
            if (!is_dir($dir)) {
                try {
                    mkdir($dir, 0755, true);
                } catch(ErrorException $e) {
                    return "Impossible de créer l'arborescence '" . $dir . "'";
                }
            }
        }

        // Création/écrasement du fichier
        try {
            file_put_contents($this->path, $this->content);
        } catch(ErrorException $e) {
            return "Impossible de créer le fichier '" . $this->path . "'";
        }

        return true;
    }

    /**
     * Retourne le chemin vers le fichier
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Retourne le chemin vers le fichier de sauvegarde
     *
     * @return string
     */
    public function getBackupPath()
    {
        $xPath = explode('.', $this->path);
        $ext = array_pop($xPath);
        $path = implode('.', $xPath);

        return $path . '.backup-' . date('YmdHis') . '.' . $ext;
    }

    /**
     * Retourne l'opération sélectionnée pour le fichier
     *
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Retourne vrai si le fichier sera sauvegardé
     *
     * @return bool
     */
    public function shouldBackup()
    {
        return $this->doBackup;
    }
}
