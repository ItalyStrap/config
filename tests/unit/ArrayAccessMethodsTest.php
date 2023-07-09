<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use ArrayIterator;
use \ItalyStrap\Config\Config;
use ItalyStrap\Config\Config_Interface;
use ItalyStrap\Config\ConfigFactory;
use ItalyStrap\Config\ConfigInterface;
use stdClass;
use function array_replace_recursive;
use function is_array;
use function json_encode;

class ArrayAccessMethodsTest extends TestCase
{

    protected function makeInstance($val = [], $default = []): Config
    {
        $sut = new Config($val, $default);
        $this->assertInstanceOf(Config_Interface::class, $sut);
        $this->assertInstanceOf(ConfigInterface::class, $sut);
        return $sut;
    }

    /**
     * @test
     */
    public function offSetMethods(): void
    {
        $sut = $this->makeInstance();
        $sut->offsetSet('key', 42);
        $this->assertTrue($sut->offsetExists('key'), '');
        $this->assertSame($sut->offsetGet('key'), 42, '');
        $sut->offsetUnset('key');
        $this->assertFalse($sut->offsetExists('key'), '');
    }

    /**
     * @test
     */
    public function itShouldHasCorrectItemsOnGetArrayCopy(): void
    {
        $arr1 = [ 'key' => 'Ciao' ];
        $arr2 = [ 'otherKey'    => 'Ariciao' ];

        $arrMerged = \array_replace_recursive($arr1, $arr2);


        $config = $this->makeInstance($arr1);
        $config->merge($arr2);

        $this->assertSame($arrMerged, $config->getArrayCopy());
    }

    /**
     * @test
     */
    public function itShouldBeCountable(): void
    {
        $config = $this->makeInstance($this->config_arr);

        $this->assertCount(count($this->config_arr), $config);
    }

    /**
     * @test
     */
    public function itShouldExchangeArrayWorksAsExpected(): void
    {
        $array = ['test' => 'val1', 'test2' => 'val2'];
        $sut = $this->makeInstance($array);

        $this->assertCount(2, $sut, '');

        $sut->add('new-key', 'new-value');
        $this->assertCount(3, $sut, '');

        $sut->remove('test', 'test2');
        $this->assertCount(1, $sut, '');

        $sut->merge(['add-key' => 'add-value']);
        $this->assertCount(2, $sut, '');
    }

    /**
     * @test
     */
    public function itShouldExchangeStorageWhenCloned(): void
    {
        $array = [
            1 => 'Numeric Index',
            'test' => [
                'sub-test' => 'Sub string index',
            ],
        ];

        $sut = $this->makeInstance($array);

        $new_sut = clone $sut;

        $this->assertEmpty($new_sut->__serialize()[1], '');
    }

    /**
     * @test
     */
    public function itShouldExchangeStorageForGetIterator(): void
    {
        $array = [
            1 => 'Numeric Index',
            'test' => [
                'sub-test' => 'Sub string index',
            ],
        ];

        $sut = $this->makeInstance($array);

        foreach ($sut as $k => $v) {
            $this->assertTrue(true);
        }

        /** @var \ArrayIterator $iterator */
        $iterator = $sut->getIterator();

        while ($iterator->valid()) {
            $iterator->key();
            $iterator->current();
            $iterator->next();
        }

        $new_sut = clone $sut;

        foreach ($new_sut as $k => $v) {
            $this->fail();
        }

        /** @var \ArrayIterator $new_iterator */
        $new_iterator = $new_sut->getIterator();

        while ($new_iterator->valid()) {
            $this->fail();
        }
    }

	public function testCallAllArrayAccessMethods()
	{
		$sut = $this->makeInstance();

		$sut['key'] = 'value';
		$this->assertTrue(isset($sut['key']));
		$this->assertSame('value', $sut['key']);
		unset($sut['key']);
		$this->assertFalse(isset($sut['key']));

		$sut->offsetSet('key', 'value');
		$this->assertTrue($sut->offsetExists('key'));
		$this->assertSame('value', $sut->offsetGet('key'));
		$sut->offsetUnset('key');
		$this->assertFalse($sut->offsetExists('key'));

		$this->assertIsArray($sut->getArrayCopy());
		$this->assertSame([], $sut->getArrayCopy());
		$this->assertCount(0, $sut);
		$this->assertSame(0, $sut->count());

		$sut->offsetSet('key', 'value');
		$this->assertSame(['key' => 'value'], $sut->getArrayCopy());
		$this->assertCount(1, $sut);
		$this->assertSame(1, $sut->count());

		// Test clone
		$new_sut = clone $sut;
		$this->assertNotSame($sut, $new_sut);
		$this->assertSame([], $new_sut->getArrayCopy());
		$this->assertCount(0, $new_sut);
		$this->assertSame(0, $new_sut->count());
	}
}
