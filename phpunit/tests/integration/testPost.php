<?php
namespace Tests\WPLib\Tests\Integration;

use Tests\WPLib\WPLib_Test_Case;
use WPLib_Page;
use WPLib_Post;

/**
 * @coversDefaultClass \WPlib_Post_Model_Base
 */
class testPost extends WPLib_Test_Case {

    /**
     * @var \WP_Post
     */
    protected $_post_id = 0;

    /**
     * @var WPLib_Post
     */
    protected $_sut;

    /**
     * 
     */
    function setUp(): void {

        $this->_post_id = $this->factory()->post->create(['post_author' => 2]);
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
     * @covers ::post_type
     */
    function testPostType() {

        $this->assertEquals('post', $this->_sut->post_type());
    
    }

    /**
     * @covers ::post_type
     *
     * @return void
     * 
     * @todo WPLIB::trigger_error is being called, why does this test not catch it?
     */
    function testPostTypeMismatch() {
        
        $this->markTestIncomplete();

        $post = $this->factory()->post->create_and_get(['post_type' => 'page']);
        $page = new WPLib_Post($post);
        
        $this->assertEquals('page', get_post_type($post));
        $this->expectNotice();
        $page->post_type();
        
    }

    /**
     * @covers ::permalink
     * @covers ::url
     * 
     * @depends testPostType
     */
    function testPermalink() {

        $this->assertEquals(get_the_permalink($this->_post_id), $this->_sut->permalink());

        $post = new WPLib_Post(null);
        $this->assertNull($post->permalink());

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
     * @covers ::is_published
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

    /**
     * 
     * @todo add additional assertions to cover all paths
     * 
     * @covers ::__call
     * @return void
     */
    function testCall() {

        $test = $this->_sut;
        $post = get_post($this->_post_id);

        $this->assertEquals($this->_post_id, $test->ID());
        $this->assertEquals($post->menu_order, $test->menu_order());
        $this->markTestIncomplete();

    }

    /**
     * @covers ::is_single
     * @return void
     */
    function testIsSingle() {

        $this->markTestIncomplete();

    }

    /**
     * @covers ::unix_timestamp
     *
     * @return void
     */
    function testUnixTimestamp() {

        /**
         * @var \WP_Post $post
         */
        $time = $this->_sut->unix_timestamp();
        $post = $this->_sut->post();

        $this->assertIsInt($time);
        $this->assertMatchesRegularExpression('#^[0-9]*$#', $time);

    }

    /**
     * @covers ::unix_timestamp_gmt
     *
     * @return void
     */
    function testUnixTimestampGMT() {

        $time = $this->_sut->unix_timestamp_gmt();

        $this->assertIsInt($time);
        $this->assertMatchesRegularExpression('#^[0-9]*$#', $time);

    }

    /**
     * @covers ::modified_unix_timestamp
     *
     * @return void
     */
    function testModifiedUnixTimestamp() {

        $time = $this->_sut->modified_unix_timestamp();

        $this->assertIsInt($time);
        $this->assertMatchesRegularExpression('#^[0-9]*$#', $time);

    }

    /**
     * @covers ::modified_unix_timestamp_gmt
     *
     * @return void
     */
    function testModifiedUnixTimestampGMT() {

        $time = $this->_sut->modified_unix_timestamp_gmt();

        $this->assertIsInt($time);
        $this->assertMatchesRegularExpression('#^[0-9]*$#', $time);

    }

    /**
     * @covers ::iso8601_date
     *
     * @return void
     */
    function testIso8601Date() {

        $time = $this->_sut->iso8601_date();

        $this->assertIsString($time);
        $this->assertMatchesRegularExpression('#^\d{4}-(0[0-9]|1[012])-([012][1-9]|3[01])T([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])\+\d{2}:\d{2}$#', $time);

    }

    /**
     * @covers ::iso8601_date_gmt
     *
     * @return void
     */
    function testIso8601DateGMT() {

        $time = $this->_sut->iso8601_date_gmt();

        $this->assertIsString($time);
        $this->assertMatchesRegularExpression('#^\d{4}-(0[0-9]|1[012])-([012][1-9]|3[01])T([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])\+\d{2}:\d{2}$#', $time);

    }

    /**
     * @covers ::iso8601_modified_date
     *
     * @return void
     */
    function testIso8601ModifiedDate() {

        $time = $this->_sut->iso8601_modified_date();

        $this->assertIsString($time);
        $this->assertMatchesRegularExpression('#^\d{4}-(0[0-9]|1[012])-([012][1-9]|3[01])T([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])\+\d{2}:\d{2}$#', $time);

    }

    /**
     * @covers ::iso8601_modified_date_gmt
     *
     * @return void
     */
    function testIso8601ModifiedDateGMT() {

        $time = $this->_sut->iso8601_modified_date_gmt();

        $this->assertIsString($time);
        $this->assertMatchesRegularExpression('#^\d{4}-(0[0-9]|1[012])-([012][1-9]|3[01])T([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])\+\d{2}:\d{2}$#', $time);

    }

    /**
     * @covers ::datetime
     *
     * @return void
     */
    function testDateTime() {

        $dtm = $this->_sut->datetime();

        $this->assertIsString($dtm);
        $this->assertNotEmpty($dtm);
        $this->assertMatchesRegularExpression('#^(January|February|March|April|May|June|July|August|September|October|November|December)\s(\d|[12]\d|[3[01]),\s\d{4}$#', $dtm);

    }

    /**
     * @covers ::modified_datetime
     *
     * @return void
     */
    function testModifiedDateTime() {

        $dtm = $this->_sut->modified_datetime();

        $this->assertIsString($dtm);
        $this->assertNotEmpty($dtm);
        $this->assertMatchesRegularExpression('#^(January|February|March|April|May|June|July|August|September|October|November|December)\s(\d|[12]\d|[3[01]),\s\d{4}$#', $dtm);

    }

    /**
     * @covers ::posted_on_values
     *
     * @return void
     */
    function testPostedOnValues() {

        $values = $this->_sut->posted_on_values();

        $this->assertIsObject($values);
        $this->assertObjectHasAttribute('iso8601_date', $values);
        $this->assertObjectHasAttribute('iso8601_modified_date', $values);
        $this->assertObjectHasAttribute('datetime', $values);
        $this->assertObjectHasAttribute('modified_datetime', $values);

    }

    /**
     * @covers ::author_id
     *
     * @return void
     */
    function testAuthorId() {

        $post = get_post($this->_post_id);
        $this->assertGreaterThan(0, $post->post_author);
        $this->assertNotFalse($this->_sut->author_id());
        $this->assertEquals($post->post_author, $this->_sut->author_id());

    }


}
