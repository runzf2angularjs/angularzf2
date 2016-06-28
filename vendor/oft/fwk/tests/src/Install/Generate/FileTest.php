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

namespace Oft\Install\Generate {

    use Oft\Test\Install\Generate\FileTest;

    function is_file($path)
    {
        return FileTest::$isFile;
    }

    function file_get_contents($path)
    {
        if (FileTest::$fileGetContents === false) {
            throw new \ErrorException();
        }
        return FileTest::$fileGetContents;
    }

    function is_dir($dir)
    {
        return FileTest::$isDir;
    }

    function mkdir($dir, $mode, $recursive)
    {
        if (FileTest::$mkdir === false) {
            throw new \ErrorException();
        }
        return true;
    }

    function file_put_contents($path, $content)
    {
        if (FileTest::$filePutContents === false) {
            throw new \ErrorException();
        }
        return FileTest::$filePutContents;
    }
}

namespace Oft\Test\Install\Generate {

    use PHPUnit_Framework_TestCase;
    use Oft\Install\Generate\File;

    class FileTest extends PHPUnit_Framework_TestCase
    {

        /**
         * Variables de référence pour les mock
         */
        public static $isFile;
        public static $fileGetContents;
        public static $isDir;
        public static $filePutContents;
        public static $mkdir;

        public function testConstructCreateOperation()
        {
            self::$isFile = false;

            $file = new File('file', 'content');

            $this->assertEquals(File::CREATE, $file->getOperation());
        }

        public function testConstructOverwriteOperation()
        {
            self::$isFile = true;
            self::$fileGetContents = 'content';

            $file = new File('file', 'content' . 'DIFF'); // Different content

            $this->assertEquals(File::OVERWRITE, $file->getOperation());
        }

        public function testConstructOverwriteNoBackupOperation()
        {
            self::$isFile = true;
            self::$fileGetContents = 'content';

            $file = new File('file', 'content' . 'DIFF', false); // Different content, no backup

            $this->assertEquals(false, $file->shouldBackup());
        }

        public function testConstructSkipBecauseNotOverwriteIfExistsFlag()
        {
            self::$isFile = true;
            self::$fileGetContents = 'content';

            $file = new File('file', 'content' . 'DIFF', true, false);

            $this->assertEquals(File::SKIP, $file->getOperation());
        }

        public function testConstructSkipOperation()
        {
            self::$isFile = true;
            self::$fileGetContents = 'content';

            $file = new File('file', 'content'); // Same content

            $this->assertEquals(File::SKIP, $file->getOperation());
        }

        public function testGetPath()
        {
            $path = 'path\to/file\with/mixed\directory/separator';

            $file = new File($path, 'content');

            $actual = $file->getPath();
            $expected = 'path/to/file/with/mixed/directory/separator';

            $this->assertEquals($expected, $actual);
        }

        public function testBackup()
        {
            self::$fileGetContents = 'content';
            self::$fileGetContents = true;
            self::$filePutContents = true;

            $file = new File('content', 'new content');

            $result = $file->backup();

            $this->assertTrue($result);
        }

        public function testBackupNoBackup()
        {
            $file = new File('content', 'new content', false);

            $result = $file->backup();

            $this->assertFalse($result);
        }

        public function testBackupFailsGetFile()
        {
            self::$fileGetContents = 'content';
            self::$filePutContents = false;

            $file = new File('content', 'new content');

            $result = $file->backup();

            $this->assertStringStartsWith("Impossible de créer le fichier de sauvegarde", $result);
        }

        public function testBackupFailsCreateFile()
        {
            self::$fileGetContents = 'content';
            self::$fileGetContents = true;
            self::$filePutContents = false;

            $file = new File('content', 'new content');

            $result = $file->backup();

            $this->assertStringStartsWith("Impossible de créer le fichier de sauvegarde", $result);
        }

        public function testSaveOverwrite()
        {
            self::$isFile = true;
            self::$fileGetContents = 'content';
            self::$isDir = true; // no mkdir
            self::$filePutContents = true;

            $file = new File('content', 'new content');

            $result = $file->save();

            $this->assertTrue($result);
        }

        public function testSaveOverwriteFailsCreateFile()
        {
            self::$isFile = true;
            self::$fileGetContents = 'content';
            self::$isDir = true;
            self::$filePutContents = false; // fail

            $file = new File('file', 'new content');

            $result = $file->save();

            $this->assertStringStartsWith("Impossible de créer le fichier", $result);
        }

        public function testSaveCreateInSubDirectory()
        {
            self::$isFile = false; // create
            self::$fileGetContents = 'content';
            self::$isDir = false; // force use mkdir
            self::$filePutContents = true;

            $file = new File('file', 'new content');

            $result = $file->save();

            $this->assertTrue($result);
        }

        public function testSaveCreateInSubDirectoryFailsMkdir()
        {
            self::$isFile = false; // create
            self::$isDir = false; // force use mkdir
            self::$mkdir = false; // mkdir throws exception

            $file = new File('file', 'new content');

            $result = $file->save();

            $this->assertStringStartsWith("Impossible de créer l'arborescence", $result);
        }

    }
}