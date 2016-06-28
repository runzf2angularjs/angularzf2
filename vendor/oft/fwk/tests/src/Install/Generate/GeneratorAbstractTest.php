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

namespace Oft\Test\Install\Generate;

use Mockery;
use Oft\Install\Generate\File;
use Oft\Install\Generate\GeneratorAbstract;
use Oft\Test\Install\Generate\Generator;
use PHPUnit_Framework_TestCase;

class Generator extends GeneratorAbstract
{

    public $attr = null;
    public $notexists = null;

    public function generate()
    {
        return null;
    }

    public function setAttr($value)
    {
        $this->attr = $value;
    }
}

class FileMockForGenerator extends File
{

    public $operation;

    public function __construct($path = null, $content = null, $doBackup = true)
    {
        if (!$path) {
            $path = '/';
        }

        if (!$content) {
            $content = 'content';
        }

        parent::__construct($path, $content, $doBackup);
    }

    public function getOperation()
    {
        return $this->operation;
    }

}

class GeneratorAbstractTest extends PHPUnit_Framework_TestCase
{

    protected $generator;

    protected function setUp()
    {
        $this->generator = new Generator();
    }

    protected function tearDown()
    {
        $this->generator = null;
    }

    public function testAddMessage()
    {
        $message = 'message test';

        $this->generator->addMessage($message);
        $messages = $this->generator->getMessages();

        $actual = $messages[0];
        $expected = $message;

        $this->assertEquals($expected, $actual);
    }

    public function testGetFiles()
    {
        $create = new FileMockForGenerator();
        $create->operation = File::CREATE;

        $skip = new FileMockForGenerator();
        $skip->operation = File::SKIP;

        $overwrite = new FileMockForGenerator();
        $overwrite->operation = File::OVERWRITE;

        $this->generator->addFile($create);
        $this->generator->addFile($skip);
        $this->generator->addFile($overwrite);

        $this->assertEquals(array($create), $this->generator->getFiles(File::CREATE));
        $this->assertEquals(array($skip), $this->generator->getFiles(File::SKIP));
        $this->assertEquals(array($overwrite), $this->generator->getFiles(File::OVERWRITE));

        $this->assertEquals(array($create,$skip,$overwrite), $this->generator->getFiles());
    }

    public function testAddMessageInfo()
    {
        $message = 'message test';

        $this->generator->addMessage($message, 'info');
        $messages = $this->generator->getMessages();

        $actual = $messages[0];
        $expected = '<info>' . $message . '</info>';

        $this->assertEquals($expected, $actual);
    }

    public function testGetTemplateDir()
    {
        $dir = $this->generator->getTemplateDir();

        $this->assertInternalType('string', $dir);
    }

    public function testSaveSkip()
    {
        $file = Mockery::mock('Oft\Install\Generate\File');
        $file->shouldReceive('getOperation')
            ->once()
            ->withNoArgs()
            ->andReturn(File::SKIP);
        $file->shouldReceive('save')
            ->never(); // Pas de sauvegarde sur fichier "skip"

        $this->generator->addFile($file);

        $return = $this->generator->save();

        $this->assertTrue($return);
    }

    public function testSaveCreateWithNoError()
    {
        $file = Mockery::mock('Oft\Install\Generate\File');
        $file->shouldReceive('getOperation')
            ->twice()
            ->withNoArgs()
            ->andReturn(File::CREATE);
        $file->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturnNull();
        $file->shouldReceive('save')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        $this->generator->addFile($file);

        $return = $this->generator->save();

        $this->assertTrue($return);
    }

    public function testSaveOverwriteWithNoError()
    {
        $file = Mockery::mock('Oft\Install\Generate\File');
        $file->shouldReceive('getOperation')
            ->twice()
            ->withNoArgs()
            ->andReturn(File::OVERWRITE);
        $file->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturnNull();
        $file->shouldReceive('backup')
            ->once()
            ->withNoArgs()
            ->andReturn(true);
        $file->shouldReceive('getBackupPath')
            ->once()
            ->withNoArgs()
            ->andReturn('/');
        $file->shouldReceive('save')
            ->once()
            ->withNoArgs()
            ->andReturn(true);
        $file->shouldReceive('shouldBackup')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        $this->generator->addFile($file);

        $return = $this->generator->save();

        $this->assertTrue($return);
    }

    public function testSaveOverwriteWithError()
    {
        $file = Mockery::mock('Oft\Install\Generate\File');
        $file->shouldReceive('getOperation')
            ->twice()
            ->withNoArgs()
            ->andReturn(File::OVERWRITE);
        $file->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturnNull();
        $file->shouldReceive('backup')
            ->once()
            ->withNoArgs()
            ->andReturn('error message');
        $file->shouldReceive('getBackupPath')
            ->once()
            ->withNoArgs()
            ->andReturn('/');

        $this->generator->addFile($file);

        $return = $this->generator->save();

        $this->assertFalse($return);
    }

    public function testSaveCreateWithError()
    {
        $file = Mockery::mock('Oft\Install\Generate\File');
        $file->shouldReceive('getOperation')
            ->twice()
            ->withNoArgs()
            ->andReturn(File::CREATE);
        $file->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturnNull();
        $file->shouldReceive('save')
            ->once()
            ->withNoArgs()
            ->andReturn('error message');

        $this->generator->addFile($file);

        $return = $this->generator->save();

        $this->assertFalse($return);
    }

    public function testConfirmCreateAndOverwrite()
    {
        $input = \Mockery::mock('Symfony\Component\Console\Input\InputInterface');
        $output = \Mockery::mock('Symfony\Component\Console\Output\OutputInterface');
        $questionHelper = \Mockery::mock('Symfony\Component\Console\Helper\QuestionHelper');

        // CREATE FILE
        $create = new FileMockForGenerator('path/to/file', 'content');
        $create->operation = FileMockForGenerator::CREATE;
        $this->generator->addFile($create);

        // OVERWRITE FILE
        $overwrite = new FileMockForGenerator('path/to/file', 'content');
        $overwrite->operation = FileMockForGenerator::OVERWRITE;
        $this->generator->addFile($overwrite);

        $input->shouldReceive('isInteractive')
            ->once()
            ->andReturn(true);
        
        // CREATE FILE
        $output->shouldReceive('writeln')
            ->once()
            ->with("Les fichiers suivants vont être créés :")
            ->andReturnNull();
        $output->shouldReceive('writeln')
            ->once()
            ->with('<comment> ' . $create->getPath() . '</comment>')
            ->andReturnNull();

        // OVERWRITE FILE
        $output->shouldReceive('writeln')
            ->once()
            ->with("Les fichiers suivants vont être écrasés :")
            ->andReturnNull();
        $output->shouldReceive('writeln')
            ->once()
            ->with('<comment> ' . $overwrite->getPath() . '</comment>')
            ->andReturnNull();
        $output->shouldReceive('writeln')
            ->once()
            ->with('<comment> -> (backup) ' . $overwrite->getBackupPath() . '</comment>')
            ->andReturnNull();

        $questionHelper->shouldReceive('ask')
            ->withAnyArgs()
            ->andReturn('y');

        $result = $this->generator->confirm($input, $output, $questionHelper);

        $this->assertTrue($result);
    }

    public function testConfirmSkipNoConfirm()
    {
        $input = \Mockery::mock('Symfony\Component\Console\Input\InputInterface');
        $output = \Mockery::mock('Symfony\Component\Console\Output\OutputInterface');
        $questionHelper = \Mockery::mock('Symfony\Component\Console\Helper\QuestionHelper');

        // SKIP FILE
        $skip = new FileMockForGenerator();
        $skip->operation = FileMockForGenerator::SKIP;
        $this->generator->addFile($skip);

        $input->shouldReceive('isInteractive')
            ->once()
            ->andReturn(true);
        
        // SKIP FILE
        $output->shouldReceive('writeln')
            ->once()
            //->with("Les fichiers suivants existent déjà et sont inchangés :")
            ->andReturnNull();
        $output->shouldReceive('writeln')
            ->once()
            ->with('<comment> ' . $skip->getPath() . '</comment>')
            ->andReturnNull();

        $questionHelper->shouldReceive('ask')
            ->withAnyArgs()
            ->andReturn('y');

        $result = $this->generator->confirm($input, $output, $questionHelper);

        $this->assertTrue($result);
    }

    public function testConfirmFalse()
    {
        $input = \Mockery::mock('Symfony\Component\Console\Input\InputInterface');
        $output = \Mockery::mock('Symfony\Component\Console\Output\OutputInterface');
        $questionHelper = \Mockery::mock('Symfony\Component\Console\Helper\QuestionHelper');

        $file = new File('/path/to/file', 'test');
        $this->generator->addFile($file);
        
        $input->shouldReceive('isInteractive')
            ->once()
            ->andReturn(true);
        
        $output->shouldReceive('writeln')
            ->once()
            ->with("Les fichiers suivants vont être créés :")
            ->andReturnNull();
        $output->shouldReceive('writeln')
            ->once()
            ->with('<comment> ' . $file->getPath() . '</comment>')
            ->andReturnNull();

        $questionHelper->shouldReceive('ask')
            ->withAnyArgs()
            ->andReturn('not-y-and-not-yes');

        $result = $this->generator->confirm($input, $output, $questionHelper);

        $this->assertFalse($result);
    }
    
    /**
     * Bug #818
     * L'option -n est prise en compte et bypass les questions
     */
    public function testConfirmNotInteractive()
    {
        $input = \Mockery::mock('Symfony\Component\Console\Input\InputInterface');
        $output = \Mockery::mock('Symfony\Component\Console\Output\OutputInterface');
        $questionHelper = \Mockery::mock('Symfony\Component\Console\Helper\QuestionHelper');

        $file = new File('/path/to/file', 'test');
        $this->generator->addFile($file);
        
        $input->shouldReceive('isInteractive')
            ->once()
            ->andReturn(false);
        
        $output->shouldReceive('writeln')
            ->once()
            ->with("Les fichiers suivants vont être créés :")
            ->andReturnNull();
        $output->shouldReceive('writeln')
            ->once()
            ->with('<comment> ' . $file->getPath() . '</comment>')
            ->andReturnNull();

        $questionHelper->shouldReceive('ask')
            ->withAnyArgs()
            ->andReturn('not-y-and-not-yes');

        $result = $this->generator->confirm($input, $output, $questionHelper);

        $this->assertTrue($result);
    }

    public function testRender()
    {
        $expected = 'test';
        $template = 'template';
        $variables = array(
            'var' => $expected,
        );

        $actual = $this->generator->render($template, $variables);

        $this->assertEquals($expected, $actual);
    }

    public function testAddSuccessMessage()
    {
        $beforeMessages = $this->generator->getMessages();

        $this->generator->addSuccessMessage();

        $afterMessages = $this->generator->getMessages();

        $this->assertEquals(count($beforeMessages) + 1, count($afterMessages));
    }

    public function testAddCancelMessage()
    {
        $beforeMessages = $this->generator->getMessages();

        $this->generator->addCancelMessage();

        $afterMessages = $this->generator->getMessages();

        $this->assertEquals(count($beforeMessages) + 1, count($afterMessages));
    }

}
