<?php
namespace Tests\WPLib\Tests\Integration;

use Tests\WPLib\WPLib_Test_Case;
use WPLib_Post;

/**
 * @coversDefaultClass \WPlib_Post_Model_Base
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
    function setUp(): void {

        $this->_post_id = $this->factory()->post->create();
        $this->_sut     = new WPLib_Post(get_post($this->_post_id));

    }

    /**
     * @covers ::has_post
     */
    function testHasPost() {
        
        $this->assertTrue($this->_sut->has_post());

    }

    /**
     * @depends testHasPost
     * @covers ::post
     * @covers ::ID
     * @covers ::title
     */
    function testPost() {

        $this->assertNotEmpty($this->_post_id);
        $post = get_post($this->_post_id);

        $this->assertInstanceOf('\WP_Post', $post);
        $this->assertInstanceOf('\WP_Post', $this->_sut->post());
        $this->assertEquals($post, $this->_sut->post());

    }

    /**
     * @depends testPost
     * @covers ::set_post
     */
    function testSetPost() {

         $this->_sut->set_post($post = $this->factory()->post->create_and_get());

         $this->assertInstanceOf('WP_Post', $post);
         $this->assertEquals($post, $this->_sut->post());

    }

    /**
     * @covers ::ID
     */
    function testID() {
        
        $this->assertEquals($this->_post_id, $this->_sut->ID());

    }

    /**
     * @covers WPLib_Post_Model_Base::title
     */
    function testTitle() {

        $post = get_post($this->_post_id);
        $this->assertEquals($post->post_title, $this->_sut->title());

    }

    /**
     * @covers ::slug
     */
    function testSlug() {
        
        $post = get_post($this->_post_id);
        $this->assertEquals($post->post_name, $this->_sut->slug());

    }

    /**
     * @covers ::has_parent
     */
    function testHasParent() {
        
        $post = new WPLIB_Post($this->factory()->post->create_and_get([
            'post_parent' => $this->_post_id,
        ]));
        
        $this->assertTrue($post->has_parent());

    }

    /**
     * @covers ::parent_id
     */
    function testParentID() {

        $post = new WPLIB_Post($this->factory()->post->create_and_get([
            'post_parent' => $this->_post_id,
        ]));

        $this->assertEquals($this->_post_id, $post->parent_id() );

    }

    /**
     * @covers ::permalink
     */
    function testPermalink() {

        $this->assertEquals(get_the_permalink($this->_post_id), $this->_sut->permalink());

    }

    /**
     * @covers ::post_type
     */
    function testPostType() {

        $this->assertEquals('post', $this->_sut->post_type());
        
    }

    /**
     * @covers ::is_blog_post
     */
    function testIsBlogPost() {

        $this->assertTrue($this->_sut->is_blog_post());

    }

    /**
     * @covers ::get_field_value
     */
    function testGetFieldValue() {

        $post = new WPLib_Post($this->factory()->post->create_and_get([
            'post_excerpt' => 'This is a test excerpt',
        ]));

        $this->assertEquals('This is a test excerpt', $post->get_field_value('post_excerpt'));
    }

    /**
     * When a short prefix is not set, it will be _wplib.
     * 
     * @see \WPLib::_get_raw_meta_field_name
     * @covers ::get_meta_value
     */
    function testGetMetaValue() {

        $post = new WPLib_Post($this->factory()->post->create_and_get([
            'meta_input' => [
                '_wplib_foo' => 'bar',
            ],
        ]));

        $this->assertEquals('bar', $post->get_meta_value('foo'));

    }

    /**
     * @covers ::excerpt
     */
    function testExcerpt() {

        $excerpt = apply_filters('the_excerpt', get_the_excerpt($this->_post_id));

        $this->assertNotEmpty($excerpt);
        $this->assertMatchesRegularExpression('#^<p>.*?</p>#', $excerpt);
        $this->assertGreaterThan(7,strlen($excerpt));
        $this->assertEquals($excerpt, $this->_sut->excerpt());

    }

    /**
     * @covers ::content
     */
    function testContent() {

        $post = get_post($this->_post_id);
        $this->assertEquals($post->post_content, $this->_sut->content());

    }

    /**
     * @covers ::is_published
     */
    function testIsPublished() {

        $this->assertTrue($this->_sut->is_published());

    }

    /**
     * @civers is_published
     */
    function testIsPublishedFalse() {

        $post = new WPLib_Post($this->factory()->post->create_and_get([
            'post_status' => 'draft',
        ]));

        $this->assertFalse($post->is_published());
    }
    
    /**
     * @depends testHasParent
     * @covers ::parent_post
     */
    function testParentPost() {

        $post = new WPLIB_Post($this->factory()->post->create_and_get([
            'post_parent' => $this->_post_id,
        ]));

        $this->assertEquals(get_post($this->_post_id), $post->parent_post());

    }

    /**
     * @depends testHasPost
     * @covers ::get_adjacent_post
     * @covers ::get_previous_post
     * @covers ::get_next_post
     * @covers ::has_adjacent_posts
     */
    function testAdjacentPosts() {

        $posts = [];

        for($i = 0; $i < 3; $i++) {
            $posts[] = $this->factory()->post->create_and_get();
            sleep(1);
        }

        $testing = new WPLib_Post($posts[1]);

        $this->assertEquals($posts[0], $testing->get_adjacent_post(['previous' => true]));
        $this->assertEquals($posts[0], $testing->get_previous_post(), 'WPLib_Model_Base::get_previous_post() has failed');
        $this->assertEquals($posts[2], $testing->get_adjacent_post(['previous' => false]));
        $this->assertEquals($posts[2], $testing->get_next_post(), 'WPLib_Model_Base::get_next_post() has failed');
        $this->assertTrue($testing->has_adjacent_posts(), 'WPLib_Model_Base::has_adjacent_posts() has failed.');

    }

    /**
     * @covers ::is_modified
     */
    function testIsModified() {

        $this->assertFalse($this->_sut->is_modified());

        /**
         * We must wait at least one second for post_modified to be different from post_date
         */
        sleep(1);

        $post = new WPLib_Post(wp_update_post(array_merge(get_post($this->_post_id, ARRAY_A), [
            'post_title' => 'New Post Title',
        ])));

        $this->assertTrue($post->is_modified());
    }

}
