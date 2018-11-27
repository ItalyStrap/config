<?php

use \ItalyStrap\Config\Config;

class ConfigTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $config_file_name;
    protected $default_file_name;
    protected $empty_file_name;
    
    protected $config_arr;
    protected $default_arr;
    protected $empty_arr;
    
    protected function _before()
    {
        $this->config_file_name = __DIR__ . '/../_data/config/config.php';
        $this->default_file_name = __DIR__ . '/../_data/config/config.php';
        $this->empty_file_name = __DIR__ . '/../_data/config/config.php';

        $this->config_arr = require( $this->config_file_name );
        $this->default_arr = require( $this->default_file_name );
        $this->empty_arr = require( $this->empty_file_name );
    }

    protected function _after()
    {
    }

    /**
     * @covers class::fileExists()
     */
    public function testFileExists()
    {
        $this->assertFileExists( $this->config_file_name );
        $this->assertFileExists( $this->default_file_name );
        $this->assertFileExists( $this->empty_file_name );
    }

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $config = new Config();
        $this->assertInstanceOf( '\ItalyStrap\Config\Config', $config );
        $this->assertInstanceOf( '\ItalyStrap\Config\Config_Interface', $config );

        $config = new Config( [] );
        $this->assertInstanceOf( '\ItalyStrap\Config\Config', $config );
        $this->assertInstanceOf( '\ItalyStrap\Config\Config_Interface', $config );

        $config = new Config( [], [] );
        $this->assertInstanceOf( '\ItalyStrap\Config\Config', $config );
        $this->assertInstanceOf( '\ItalyStrap\Config\Config_Interface', $config );

        $config = new Config( $this->config_arr, $this->default_arr );
        $this->assertInstanceOf( '\ItalyStrap\Config\Config', $config );
        $this->assertInstanceOf( '\ItalyStrap\Config\Config_Interface', $config );
    }

    /**
     * @test
     * it should have key
     */
    public function it_should_have_key()
    {

        $config = new Config( $this->config_arr );

        $this->assertTrue( $config->has( 'tizio' ) );
        $this->assertTrue( $config->has( 'caio' ) );
        $this->assertTrue( $config->has( 'sempronio' ) );
        $this->assertFalse( $config->has( 'cesare' ) );
    }

    /**
     * @test
     * it should get_key
     */
    public function it_should_get_key()
    {

        $config = new Config( $this->config_arr );

        $this->assertEquals( [], $config->get( 'tizio' ) );
    }

    /**
     * @test
     * it should return_null_if_key_does_not_exists
     */
    public function it_should_return_null_if_key_does_not_exists()
    {

        $config = new Config( $this->config_arr );

        $this->assertEquals( null, $config->get( 'noKey' ) );
    }

    /**
     * @test
     * it should return_the_given_value_if_key_does_not_exists
     */
    public function it_should_return_the_given_value_if_key_does_not_exists()
    {

        $config = new Config( $this->config_arr );

        $this->assertEquals( true, $config->get( 'noKey', true ) );
    }

    /**
     * @test
     * it should array_replace_recursively
     */
    public function it_should_array_replace_recursively()
    {

        $config = new Config( $this->config_arr, $this->default_arr );

        $this->assertTrue( ! is_array( $config->get( 'recursive' ) ) );

        $this->assertEquals( 'not an array', $config->get( 'recursive' ) );

    }

    /**
     * @test
     * it should return_an_array
     */
    public function it_should_return_an_array()
    {

        $config = new Config( $this->config_arr );

        $this->assertTrue( is_array( $config->all() ) );
        $this->assertEquals( $this->config_arr, $config->all() );

    }

    /**
     * @test
     * it should add_new_item
     */
    public function it_should_add_new_item()
    {

        $config = new Config( $this->config_arr, $this->default_arr );
        $config->push( 'new_item', true );

        $this->assertTrue( $config->get( 'new_item' ) );

    }

    /**
     * @test
     * it should merge_given_array
     */
    public function it_should_merge_given_array()
    {

        $config = new Config( $this->config_arr, $this->default_arr );

        $new_array = [
            'new_key'   => 'New Value',
        ];

        $config->merge( $new_array );

        $this->assertEquals( 'New Value', $config->get( 'new_key' ) );

    }
}
