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
        $this->default_file_name = __DIR__ . '/../_data/config/default.php';
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

        $config = new Config( $this->config_arr );
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
	 * it should return_an_array
	 */
	public function it_should_return_an_array()
	{

		$config = new Config( $this->config_arr );

		$this->assertTrue( is_array( $config->all() ) );
		$this->assertEquals( $this->config_arr, $config->all() );
		$this->assertEquals( $config->all(), $config->getArrayCopy() );
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
     * it should replace_recursively
     */
    public function it_should_replace_recursively()
    {

        $config = new Config( $this->default_arr );
		$this->assertEquals( $this->default_arr['recursive'], $config->get( 'recursive' ) );

        $config = new Config( $this->config_arr, $this->default_arr );
        $this->assertEquals( $this->config_arr['recursive'], $config->get( 'recursive' ) );
        $this->assertEquals( $this->config_arr['recursive'], $config->recursive );
        $this->assertEquals( $this->config_arr['recursive']['subKey'], $config->recursive['subKey'] );

        $this->assertNotEquals( $this->default_arr['recursive'], $config->get( 'recursive' ) );
		$this->assertNotEquals( $this->default_arr['recursive'], $config->recursive );
		$this->assertNotEquals( $this->default_arr['recursive']['subKey'], $config->recursive['subKey'] );

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
			'recursive' => [
				'subKey'	=> 'otherSubValue',
			],
        ];

        $config->merge( $new_array );

        $this->assertEquals( 'New Value', $config->get( 'new_key' ) );

		$this->assertEquals( $new_array['recursive'], $config->get( 'recursive' ) );
		$this->assertEquals( $new_array['recursive'], $config['recursive'] );
		$this->assertEquals( $new_array['recursive'], $config->recursive );
		$this->assertEquals( $new_array['recursive']['subKey'], $config->recursive['subKey'] );
    }

    /**
     * @test
     * it_should_be_removed
     */
    public function it_should_be_removed()
    {

        $config = new Config( $this->config_arr, $this->default_arr );
        $config->remove( 'recursive' );
        $this->assertFalse( $config->has( 'recursive' ) );

        $this->assertTrue( $config->has( 'tizio' ) );
        $this->assertTrue( $config->has( 'caio' ) );

		$config->remove( ['tizio', 'caio'] );

		$this->assertFalse( $config->has( 'tizio' ) );
		$this->assertFalse( $config->has( 'caio' ) );

		$config = new Config( $this->config_arr, $this->default_arr );
		$config->remove( ['recursive'] );
		$this->assertFalse( $config->has( 'recursive' ) );

		$config = new Config( $this->config_arr, $this->default_arr );
		$this->assertTrue( $config->has( 'recursive' ) );
		$this->assertTrue( $config->has( 'tizio' ) );
		$config->remove( ['recursive'], 'tizio' );
		$this->assertFalse( $config->has( 'recursive' ) );
		$this->assertFalse( $config->has( 'tizio' ) );
    }

    /**
     * @test
     * it_should_be
	 */
    public function it_should_be()
    {
    	$expected = 42;

        $config = new Config();
        $config->test = $expected;
        $this->assertTrue( $config->has( 'test' ) );
        $this->assertTrue( isset( $config->test ) );
        $this->assertNotTrue( $config->has( 'some' ) );
        $this->assertNotTrue( isset( $config->some ) );
        $this->assertEquals( $expected, $config->get( 'test' ) );

        $config[2] = 'value';
        $this->assertTrue( $config->has(2) );

        $config->push( 0, $expected );
        $this->assertEquals( $expected, $config->get( 0 ) );
    }

	/**
	 * @test
	 */
	public function it_should_be_iterable() {
		$arr = [ 'key' => 'val' ];
		$config = new Config( $arr );

		foreach ( $config as $key => $value ) {
			$this->assertTrue( $arr[ $key ] === $value );
		}

		foreach ( $config as $key => $value ) {
			$this->assertTrue( $config->$key === $value );
		}
    }

	/**
	 * @test
	 */
	public function it_should_be_cauntable() {

		$config = new Config( $this->config_arr );

		$this->assertCount( \count( $this->config_arr ), $config );
    }
}
