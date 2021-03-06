<?php

namespace Wikibase\View\Tests\Termbox\Renderer;

use Exception;
use MediaWiki\Http\HttpRequestFactory;
use MWHttpRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit4And6Compat;
use Wikibase\DataModel\Entity\ItemId;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\View\Termbox\Renderer\TermboxRemoteRenderer;
use Wikibase\View\Termbox\Renderer\TermboxRenderingException;

/**
 * @covers \Wikibase\View\Termbox\Renderer\TermboxRemoteRenderer
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class TermboxRemoteRendererTest extends TestCase {

	use PHPUnit4And6Compat;

	/** private */ const SSR_URL = 'https://ssr/termbox';

	public function testGetContentWithEntityIdAndLanguage_returnsRequestResponse() {
		$content = 'hello from server!';

		$request = $this->newSuccessfulRequest();
		$request->expects( $this->once() )
			->method( 'getContent' )
			->willReturn( $content );

		$client = new TermboxRemoteRenderer(
			$this->newHttpRequestFactoryWithRequest( $request ),
			self::SSR_URL
		);
		$this->assertSame(
			$content,
			$client->getContent( new ItemId( 'Q42' ), 'de', '/edit/Q42', true )
		);
	}

	/**
	 * @dataProvider requestParamsDataProvider
	 */
	public function testGetContentBuildsRequestUrl(
		array $given, array $returned
	) {
		$requestFactory = $this->newHttpRequestFactory();
		$requestFactory->expects( $this->once() )
			->method( 'create' )
			->with(
				self::SSR_URL
				. '?' . http_build_query( [
					'entity' => $returned[0],
					'language' => $returned[1],
					'editLink' => $returned[2],
					'canEdit' => $returned[3]
				] ),
				[]
			)
			->willReturn( $this->newSuccessfulRequest() );

		( new TermboxRemoteRenderer(
			$requestFactory,
			self::SSR_URL
		) )->getContent( $given[0], $given[1], $given[2], $given[3] );
	}

	public function requestParamsDataProvider() {
		yield [
			[ new ItemId( 'Q42' ), 'de', '/wiki/Special:SetLabelDescriptionAliases/Q42', true ],
			[ 'Q42', 'de', '/wiki/Special:SetLabelDescriptionAliases/Q42', 1 ]
		];

		yield [
			[ new PropertyId( 'P3' ), 'en', '/wiki/Special:SetLabelDescriptionAliases/P3', false ],
			[ 'P3', 'en', '/wiki/Special:SetLabelDescriptionAliases/P3', 0 ]
		];
	}

	public function testGetContentEncounteringUpstreamException_bubblesRequestException() {
		$entityId = new ItemId( 'Q42' );
		$language = 'de';
		$editLinkUrl = '/edit/Q42';
		$canEdit = true;

		$upstreamException = new Exception( 'domain exception' );

		$request = $this->newHttpRequest();
		$request->expects( $this->once() )
			->method( 'execute' )
			->willThrowException( $upstreamException );

		$client = new TermboxRemoteRenderer(
			$this->newHttpRequestFactoryWithRequest( $request ),
			self::SSR_URL
		);

		try {
			$client->getContent( $entityId, $language, $editLinkUrl, $canEdit );
			$this->fail( 'Expected exception did not occur.' );
		} catch ( Exception $exception ) {
			$this->assertInstanceOf( TermboxRenderingException::class, $exception );
			$this->assertSame( 'Encountered request problem', $exception->getMessage() );
			$this->assertSame( $upstreamException, $exception->getPrevious() );
		}
	}

	public function testGetContentEncounteringServerErrorResponse_throwsException() {
		$entityId = new ItemId( 'Q42' );
		$language = 'de';
		$editLinkUrl = '/edit/Q42';
		$canEdit = true;

		$request = $this->newHttpRequest();
		$request->expects( $this->once() )
			->method( 'getStatus' )
			->willReturn( 500 );
		$request->expects( $this->never() )
			->method( 'getContent' );

		$client = new TermboxRemoteRenderer(
			$this->newHttpRequestFactoryWithRequest( $request ),
			self::SSR_URL
		);

		try {
			$client->getContent( $entityId, $language, $editLinkUrl, $canEdit );
			$this->fail( 'Expected exception did not occur.' );
		} catch ( Exception $exception ) {
			$this->assertInstanceOf( TermboxRenderingException::class, $exception );
			$this->assertSame( 'Encountered bad response: 500', $exception->getMessage() );
		}
	}

	public function testGetContentEncounteringNotFoundResponse_throwsException() {
		$entityId = new ItemId( 'Q4711' );
		$language = 'de';
		$editLinkUrl = '/edit/Q4711';
		$canEdit = false;

		$request = $this->newHttpRequest();
		$request->expects( $this->once() )
			->method( 'getStatus' )
			->willReturn( 404 );
		$request->expects( $this->never() )
			->method( 'getContent' );

		$client = new TermboxRemoteRenderer(
			$this->newHttpRequestFactoryWithRequest( $request ),
			self::SSR_URL
		);

		try {
			$client->getContent( $entityId, $language, $editLinkUrl, $canEdit );
			$this->fail( 'Expected exception did not occur.' );
		} catch ( Exception $exception ) {
			$this->assertInstanceOf( TermboxRenderingException::class, $exception );
			$this->assertSame( 'Encountered bad response: 404', $exception->getMessage() );
		}
	}

	/**
	 * @return MockObject|HttpRequestFactory
	 */
	private function newHttpRequestFactory() {
		return $this->createMock( HttpRequestFactory::class );
	}

	/**
	 * @return MockObject|HttpRequestFactory
	 */
	private function newHttpRequestFactoryWithRequest( MWHttpRequest $req ) {
		$factory = $this->createMock( HttpRequestFactory::class );
		$factory->method( 'create' )
			->willReturn( $req );

		return $factory;
	}

	/**
	 * @return MockObject|MWHttpRequest
	 */
	private function newSuccessfulRequest() {
		$request = $this->newHttpRequest();
		$request->method( 'getStatus' )
			->willReturn( TermboxRemoteRenderer::HTTP_STATUS_OK );

		return $request;
	}

	/**
	 * @return MockObject|MWHttpRequest
	 */
	private function newHttpRequest() {
		$req = $this->createMock( MWHttpRequest::class );
		$req->expects( $this->once() )->method( 'execute' );

		return $req;
	}

}
