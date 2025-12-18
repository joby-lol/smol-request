<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Post;

use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function test_get_require(): void
    {
        $post = new Post(
            values: [
                'name' => 'Alice',
                'age' => '30',
            ],
            files: []
        );

        $this->assertEquals('Alice', $post->get('name'));
        $this->assertEquals('30', $post->get('age'));
        $this->assertNull($post->get('nonexistent'));
        $this->assertEquals('default', $post->get('nonexistent', 'default'));

        $this->assertEquals('Alice', $post->require('name'));

        $this->expectException(PostException::class);
        $post->require('nonexistent');
    }

    public function test_get_require_int()
    {
        $post = new Post(
            values: [
                'count' => '42',
                'height' => '175',
                'invalid' => 'abc',
            ],
            files: []
        );

        $this->assertEquals(42, $post->getInt('count'));
        $this->assertEquals(175, $post->getInt('height'));
        $this->assertNull($post->getInt('nonexistent'));
        $this->assertEquals(100, $post->getInt('nonexistent', 100));

        $this->assertEquals(42, $post->requireInt('count'));

        $this->expectException(PostException::class);
        $post->requireInt('nonexistent');

        $this->expectException(PostException::class);
        $post->getInt('invalid');
    }

    public function test_get_require_bool(): void
    {
        $post = new Post(
            values: [
                'active' => '1',
                'subscribed' => '0',
                'invalid' => 'yes',
            ],
            files: []
        );

        $this->assertTrue($post->getBool('active'));
        $this->assertFalse($post->getBool('subscribed'));
        $this->assertNull($post->getBool('nonexistent'));

        $this->assertTrue($post->requireBool('active'));

        $this->expectException(PostException::class);
        $post->requireBool('nonexistent');

        $this->expectException(PostException::class);
        $post->getBool('invalid');
    }

    public function test_get_require_float(): void
    {
        $post = new Post(
            values: [
                'price' => '19.99',
                'weight' => '2.5',
                'invalid' => 'abc',
            ],
            files: []
        );

        $this->assertEquals(19.99, $post->getFloat('price'));
        $this->assertEquals(2.5, $post->getFloat('weight'));
        $this->assertNull($post->getFloat('nonexistent'));
        $this->assertEquals(9.99, $post->getFloat('nonexistent', 9.99));

        $this->assertEquals(19.99, $post->requireFloat('price'));

        $this->expectException(PostException::class);
        $post->requireFloat('nonexistent');

        $this->expectException(PostException::class);
        $post->getFloat('invalid');
    }

    public function test_basic_file_handling(): void
    {
        $file1 = new PostFile('file1.txt', '/temp/foo/bar', 123);
        $file2 = new PostFile('file2.jpg', '/temp/foo/baz', 456);
        $file3 = new PostFile('file3.png', '/temp/foo/qux', 789);

        $post = new Post(
            values: [],
            files: [
                'file1' => [$file1],
                'multi' => [$file2, $file3]
            ]
        );

        $this->assertSame($file1, $post->file('file1'));
        $this->assertNull($post->file('nonexistent'));
        $this->assertSame($file2, $post->files('multi')[0]);
        $this->assertSame($file3, $post->files('multi')[1]);
        $this->assertEmpty($post->files('nonexistent'));
        $this->assertNull($post->file('nonexistent'));
        $this->expectException(UploadException::class);
        $post->requireFile('nonexistent');
    }

    public function test_require_files(): void
    {
        $file1 = new PostFile('file1.txt', '/temp/foo/bar', 123);
        $file2 = new PostFile('file2.jpg', '/temp/foo/baz', 456);

        $post = new Post(
            values: [],
            files: [
                'multi' => [$file1, $file2]
            ]
        );

        $files = $post->requireFiles('multi', 1, 3);
        $this->assertCount(2, $files);
        $this->assertSame($file1, $files[0]);
        $this->assertSame($file2, $files[1]);

        $this->expectException(UploadException::class);
        $post->requireFiles('multi', 3);

        $this->expectException(UploadException::class);
        $post->requireFiles('multi', 0, 1);
    }

    public function test_has(): void
    {
        $post = new Post(
            values: [
                'count' => '42',
                'height' => '175',
            ],
            files: []
        );
        $this->assertTrue($post->has('count'));
        $this->assertFalse($post->has('weight'));
    }
}
