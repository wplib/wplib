<?php
namespace Tests\WPLib\Tests\Integration;

use Tests\WPLib\WPLib_Test_Case;
use WPLib_Post;

/**
 * @coversDefaultClass \WPlib_Post
 */
class testPost extends WPLib_Test_Case {

    /**
     * @var \WP_Post
     */
    protected $_post_id;

    /**
     * @var WPLib_Post
     */
    protected $_sut;

    /**
     * 
     */
    public function setUp(): void {

        $this->_post_id = $this->factory->post->create();
        $this->_sut     = new \WPLib_Post(get_post($this->_post_id));

    }

    /**
     * @covers ::__construct
     * @covers ::__call
     * @covers \WPLib_Post_Model_Base::post
     */
    public function testWplibPost() {

        $this->assertInstanceOf('\WP_Post', $this->_sut->post());
        $this->assertEquals(get_post($this->_post_id), $this->_sut->post());

    }


}
