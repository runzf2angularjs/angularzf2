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

namespace Oft\Test\Validator;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    private $jsonValidator;
    
    public function setUp()
    {
        $this->jsonValidator = new \Oft\Validator\Json();
    }
    
    public function testScalar()
    {
        $scalars = array(1, 1.2, 'string');
        
        foreach ($scalars as $scalar) {
            $json = \Zend\Json\Json::encode($scalar);
            $this->assertTrue($this->jsonValidator->isValid($json));
        }
    }
    
    public function testArray()
    {
        $array = array(
            1,
            1.2,
            'string',
            '|.+=-()',
            array(1, 1.2, 'string')
        );
        
        $json = \Zend\Json\Json::encode($array);
        
        $this->assertTrue($this->jsonValidator->isValid($json));
    }
    
    public function testObject()
    {
        $obj = new \stdClass();
        $obj->string = 'string';
        $obj->integer = 4;
        $obj->float = 1.2;
        $obj->array = array(1, 1.2, 'string');
        
        $json = \Zend\Json\Json::encode($obj);
        
        $this->assertTrue($this->jsonValidator->isValid($json));
    }
    
    /**
     * Tests Oft_Validate_JSON->isValid()
     */
    public function testError()
    {
        $json = '}{ \ notjson';
        $this->assertFalse($this->jsonValidator->isValid($json));
        $messages = $this->jsonValidator->getMessages();
        //$this->assertInternalType('array', $messages);
        $this->assertEquals(1, count($messages));
        $this->assertArrayHasKey(\Oft\Validator\Json::JSON_INVALID, $messages);
        
        $templates = $this->jsonValidator->getMessageTemplates();
        $this->assertArrayHasKey(\Oft\Validator\Json::JSON_INVALID, $templates);
        
        $this->assertEquals(
            $messages[\Oft\Validator\Json::JSON_INVALID],
            $templates[\Oft\Validator\Json::JSON_INVALID]
        );
    }
    
    public function testJqGridFilters()
    {
        $string = '{"groupOp":"AND","rules":[{"field":"name","op":"bw","data":"Na"}]}';

        $this->assertTrue($this->jsonValidator->isValid($string));
    }
    
    public function testJqGridBugOnDate()
    {
        // La date est INVALIDE mais elle doit passer car
        // JQGrid renvoie la date sous cette forme
        // cf. http://www.json.org/
        $string = '{"groupOp":"AND","rules":[{"field":"date","op":"bw","data":"18/12/2012"}]}';
        $this->assertTrue($this->jsonValidator->isValid($string));
        
        // Date valide
        $string = '{"groupOp":"AND","rules":[{"field":"date","op":"bw","data":"18\\/12\\/2012"}]}';
        $this->assertTrue($this->jsonValidator->isValid($string));
    }
    
    public function testBug8()
    {
        // bug #8 : problème d'encodage sur les caractères accentués jqGrid
        $string = '	{"groupOp":"AND","rules":[{"field":"departement","op":"bw","data":"ariège"}]}';
        $this->assertTrue($this->jsonValidator->isValid($string));
    }
    
    
    public function testBug255()
    {
        // bug #255 : problème d'encodage sur les caractères slashes
        $string = '{"id":13,"name":"Utilisation (Macro/Micro/Indoor/Tunnel)"}';
        $this->assertTrue($this->jsonValidator->isValid($string));
    }

}
