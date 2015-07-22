<?php

namespace Wikibase\Lib\Test\Serializers;

use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\Lib\Serializers\SerializationOptions;
use Wikibase\Lib\Serializers\LibSerializerFactory;

/**
 * @covers Wikibase\Lib\Serializers\LibSerializerFactory
 *
 * @group WikibaseLib
 * @group Wikibase
 * @group WikibaseSerialization
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Daniel Kinzler
 */
class LibSerializerFactoryTest extends \MediaWikiTestCase {

	public function testConstructor() {
		new LibSerializerFactory();
		$this->assertTrue( true );
	}

	public function objectProvider() {
		$argLists = array();

		$argLists[] = array( new PropertyNoValueSnak( 42 ) );
		$argLists[] = array( new Reference() );
		$argLists[] = array( new Statement( new PropertyNoValueSnak( 42 ) ) );

		$argLists[] = array( new Item(), new SerializationOptions() );

		return $argLists;
	}

	/**
	 * @dataProvider objectProvider
	 */
	public function testNewSerializerForObject( $object, $options = null ) {
		$factory = new LibSerializerFactory();

		$serializer = $factory->newSerializerForObject( $object, $options );

		$this->assertInstanceOf( 'Wikibase\Lib\Serializers\Serializer', $serializer );

		$serializer->getSerialized( $object );
	}

	public function serializationProvider() {
		$argLists = array();

		$snak = new PropertyNoValueSnak( 42 );

		$factory = new LibSerializerFactory();
		$serializer = $factory->newSerializerForObject( $snak );

		$argLists[] = array( 'Wikibase\DataModel\Snak\Snak', $serializer->getSerialized( $snak ) );

		return $argLists;
	}

	public function entityTypeProvider() {
		return array(
			array( Item::ENTITY_TYPE ),
			array( Property::ENTITY_TYPE ),
		);
	}

	/**
	 * @dataProvider entityTypeProvider
	 */
	public function testNewUnserializerForEntity( $entityType ) {
		$factory = new LibSerializerFactory();
		$options = new SerializationOPtions();

		$unserializer = $factory->newUnserializerForEntity( $entityType, $options );

		$this->assertInstanceOf( 'Wikibase\Lib\Serializers\Unserializer', $unserializer );
	}

	/**
	 * @dataProvider entityTypeProvider
	 */
	public function testNewSerializerForEntity( $entityType ) {
		$factory = new LibSerializerFactory();
		$options = new SerializationOPtions();

		$unserializer = $factory->newSerializerForEntity( $entityType, $options );

		$this->assertInstanceOf( 'Wikibase\Lib\Serializers\Serializer', $unserializer );
	}

	public function newUnserializerProvider() {
		$names = array(
			'SnakUnserializer',
			'ReferenceUnserializer',
			'ClaimUnserializer',
			'ClaimsUnserializer',
			'PropertyUnserializer',
			'ItemUnserializer',
			'LabelUnserializer',
			'DescriptionUnserializer',
			'AliasUnserializer',
		);

		return array_map( function( $name ) {
			return array( $name );
		}, $names );
	}

	/**
	 * @dataProvider newUnserializerProvider
	 */
	public function testNewUnserializer( $serializerName ) {
		$factory = new LibSerializerFactory();
		$options = new SerializationOPtions();

		$method = "new$serializerName";
		$unserializer = $factory->$method( $options );

		$this->assertInstanceOf( 'Wikibase\Lib\Serializers\Unserializer', $unserializer );
	}

	public function newSerializerProvider() {
		$names = array(
			'SnakSerializer',
			'ReferenceSerializer',
			'ClaimSerializer',
			'ClaimsSerializer',
			'PropertySerializer',
			'ItemSerializer',
			'LabelSerializer',
			'DescriptionSerializer',
			'AliasSerializer',

			'SiteLinkSerializer',
		);

		return array_map( function( $name ) {
			return array( $name );
		}, $names );
	}

	/**
	 * @dataProvider newSerializerProvider
	 */
	public function testNewSerializer( $serializerName ) {
		$factory = new LibSerializerFactory();
		$options = new SerializationOPtions();

		$method = "new$serializerName";
		$unserializer = $factory->$method( $options );

		$this->assertInstanceOf( 'Wikibase\Lib\Serializers\Serializer', $unserializer );
	}

}
