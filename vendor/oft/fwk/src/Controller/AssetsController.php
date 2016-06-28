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

namespace Oft\Controller;

use DateTime;
use Oft\Mvc\ControllerAbstract;
use Oft\Mvc\Exception\NotFoundException;

/**
 * Contrôleur des Assets
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class AssetsController extends ControllerAbstract
{

    /**
     * Référentiel de Content-Types
     *
     * @var array
     */
    protected static $contentTypes = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    /**
     * Action de rendu d'une collection d'assets
     *
     * @todo La version ne sert plus à rien
     *
     * @param int $version Version
     * @param string $name Nom de la collection
     * @param string $type Type et index de la collection
     * @param string $resource Index et hash de la collection
     */
    public function renderAction($version, $name, $type, $resource)
    {
        $this->disableRendering();

        // Resource = hash (7 chars) + index + ".extension"
        $dotpos = strpos($resource, '.');
        $index = substr($resource, 7, $dotpos - 7);

        // Assets Collection
        $collection = $this->app->get('AssetManager')->getCollection($name, $index);

        $lastModified = $collection->getLastModified();
        $this->setCacheHeaders($lastModified);

        if ($this->isNotModified()) {
            return;
        }

        $this->send($collection->dump(), $this->getContentType($type));
    }

    /**
     * Action de rendu d'un fichier statique
     *
     * @todo La version ne sert plus à rien
     *
     * @param int $version Version
     * @param string $name Nom de la collection associée au fichier
     * @param string $type Part du chemin vers le fichier
     * @param string $resource Chemin vers le fichier
     * @throws NotFoundException
     */
    public function renderFileAction($version, $name, $type, $resource)
    {
        $this->disableRendering();

        // File
        $module = $this->app->get('AssetManager')->getCollectionModule($name);
        $root = $this->app->moduleManager->getModule($module)->getDir('assets');
        $filepath = $root . '/' . $name . '/' . $type . '/' . $resource;

        // Fix sécurité
        // Le chemin vers le fichier ne peut être en dehors du répertoire des assets
        $documentRoot = realpath($root);
        $realpath = realpath($filepath);

        if ($realpath === false || strpos($realpath, $documentRoot) !== 0) {
            throw new NotFoundException();
        }

        $lastModified = \filemtime($filepath);
        $this->setCacheHeaders($lastModified);

        if ($this->isNotModified()) {
            return;
        }

        $extension = \pathinfo($filepath, PATHINFO_EXTENSION);

        $this->send(
            file_get_contents($filepath),
            $this->getContentType($extension)
        );
    }

    /**
     * Définit les headers relatifs à la mise en cache du fichier
     *
     * @param int $lastModified
     */
    protected function setCacheHeaders($lastModified)
    {
        $time = \time();
        $days = 30;

        // DateTime pour "Expires"
        $expire = new DateTime();
        $expire->modify('+' . $days . ' days');

        // DateTime pour "Last-Modified"
        $lastm = new DateTime();
        $lastm->setTimestamp($lastModified);

        // $days en secondes
        $maxAge = \strtotime('+' . $days . ' days') - $time;

        $sfResponse = $this->response->getResponseObject();

        // Supprime le header Pragma
        $sfResponse->headers->remove('Cache-Control');

        // Supprime le header Pragma
        $sfResponse->headers->remove('Pragma');

        // Définition des paramètre du cache
        $sfResponse->setCache(array(
            //'etag',
            'last_modified' => $lastm,
            'max_age' => $maxAge,
            's_maxage' => $maxAge,
            'private' => false,
            'public' => true
        ));

        $sfResponse->setExpires($expire);
    }

    /**
     * Retourne vrai si le requête concernée est inchangée
     *
     * @return bool
     */
    protected function isNotModified()
    {
        $request = $this->request->getRequestObject();

        return $this->response
            ->getResponseObject()
            ->isNotModified($request);
    }

    /**
     * Retourne le content-type selon l'extension donnée
     *
     * @param string $extension
     * @return string
     */
    protected function getContentType($extension)
    {
        $extension = \strtolower($extension);

        if (\array_key_exists($extension, self::$contentTypes)) {
            return self::$contentTypes[$extension];
        }

        return 'application/octet-stream';
    }

}
