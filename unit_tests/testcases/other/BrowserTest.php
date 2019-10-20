<?php

/**
 * @group Browser
 */
class BrowserTest extends PHPUnit\Framework\TestCase {
	public function setUp(): void {
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !class_exists('Browser', FALSE ) ) {
			require_once( Environment::getBasePath().'/classes/other/Browser.php');
		}
	}

	public function tearDown(): void {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);
	}

	function testBrowserIE() {
		$browser = new Browser( 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; BRI/2)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '11.0' ); //Use Trident Version

		$browser = new Browser( 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/7.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '11.0' ); //Use Trident Version

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; LEN2)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '9.0' );

		$browser = new Browser( 'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '11.0' ); //Use Trident Version

		$browser = new Browser( 'Mozilla/5.0 (Windows; U; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '6.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '6.0' );

		$browser = new Browser( 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '7.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; c .NET CLR 3.0.04506; .NET CLR 3.5.30707; InfoPath.1; el-GR)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '7.0' );

		$browser = new Browser( 'Mozilla/5.0 (MSIE 8.0; Windows NT 6.1; Trident/4.0; GTB7.4; InfoPath.2; SV1; .NET CLR 3.3.69573; WOW64; en-US)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '8.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; GTB7.4; InfoPath.2; SV1; .NET CLR 3.3.69573; WOW64; en-US)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '8.0' );

		$browser = new Browser( 'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '9.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 7.1; Trident/5.0)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '9.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '10.0' );

		$browser = new Browser( 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)' );
		$this->assertEquals( $browser->getBrowser(), Browser::BROWSER_IE );
		$this->assertEquals( $browser->getVersion(), '8.0' ); //Take MSIE over Trident here. -- Internet Explorer 8 on Windows 7, Internet Explorer 10 Compatibility View

		return TRUE;
	}
}
?>
