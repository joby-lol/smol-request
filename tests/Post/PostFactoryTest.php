<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Post;

use PHPUnit\Framework\TestCase;

class PostFactoryTest extends TestCase
{
    public function test_file_array_parsing(): void
    {
        $factory = new PostFactory();

        $files = [
            // Single file upload
            'avatar' => [
                'name' => 'profile.jpg',
                'tmp_name' => '/tmp/php123456',
                'size' => 5242880,
                'error' => UPLOAD_ERR_OK,
            ],
            // Multiple file uploads
            'attachments' => [
                'name' => ['document.pdf', 'image.png', 'archive.zip'],
                'tmp_name' => ['/tmp/php654321', '/tmp/php789012', '/tmp/php345678'],
                'size' => [1024000, 2048000, 512000],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            ],
        ];

        $post = $factory->fromArrays(
            post: [],
            files: $files
        );

        $this->assertEquals(1, count($post->files['avatar']));
        $this->assertEquals('profile.jpg', $post->files['avatar'][0]->filename);
        $this->assertEquals('/tmp/php123456', $post->files['avatar'][0]->tmp_name);
        $this->assertEquals(5242880, $post->files['avatar'][0]->size);

        $this->assertEquals(3, count($post->files['attachments']));
        $this->assertEquals('document.pdf', $post->files['attachments'][0]->filename);
        $this->assertEquals('/tmp/php654321', $post->files['attachments'][0]->tmp_name);
        $this->assertEquals(1024000, $post->files['attachments'][0]->size);
        $this->assertEquals('image.png', $post->files['attachments'][1]->filename);
        $this->assertEquals('/tmp/php789012', $post->files['attachments'][1]->tmp_name);
        $this->assertEquals(2048000, $post->files['attachments'][1]->size);
        $this->assertEquals('archive.zip', $post->files['attachments'][2]->filename);
        $this->assertEquals('/tmp/php345678', $post->files['attachments'][2]->tmp_name);
        $this->assertEquals(512000, $post->files['attachments'][2]->size);
    }

    public function test_file_array_parsing_with_no_file_single_upload(): void
    {
        $factory = new PostFactory();

        $files = [
            'avatar' => [
                'name' => 'profile.jpg',
                'tmp_name' => '',
                'size' => 0,
                'error' => UPLOAD_ERR_NO_FILE,
            ],
        ];

        $post = $factory->fromArrays(
            post: [],
            files: $files
        );

        $this->assertArrayNotHasKey('avatar', $post->files);
    }

    public function test_file_array_parsing_with_error_single_upload(): void
    {
        $this->expectException(UploadException::class);

        $factory = new PostFactory();

        $files = [
            'avatar' => [
                'name' => 'profile.jpg',
                'tmp_name' => '',
                'size' => 0,
                'error' => UPLOAD_ERR_CANT_WRITE,
            ],
        ];

        $factory->fromArrays(
            post: [],
            files: $files
        );
    }

    public function test_file_array_parsing_with_no_file_multiple_upload(): void
    {
        $factory = new PostFactory();

        $files = [
            'attachments' => [
                'name' => ['document.pdf', 'image.png', 'archive.zip'],
                'tmp_name' => ['/tmp/php654321', '', '/tmp/php345678'],
                'size' => [1024000, 0, 512000],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE, UPLOAD_ERR_OK],
            ],
        ];

        $post = $factory->fromArrays(
            post: [],
            files: $files
        );

        $this->assertEquals(2, count($post->files['attachments']));
        $this->assertEquals('document.pdf', $post->files['attachments'][0]->filename);
        $this->assertEquals('/tmp/php654321', $post->files['attachments'][0]->tmp_name);
        $this->assertEquals(1024000, $post->files['attachments'][0]->size);
        $this->assertEquals('archive.zip', $post->files['attachments'][1]->filename);
        $this->assertEquals('/tmp/php345678', $post->files['attachments'][1]->tmp_name);
        $this->assertEquals(512000, $post->files['attachments'][1]->size);
    }

    public function test_file_array_parsing_with_error_multiple_upload(): void
    {
        $this->expectException(UploadException::class);

        $factory = new PostFactory();

        $files = [
            'attachments' => [
                'name' => ['document.pdf', 'image.png', 'archive.zip'],
                'tmp_name' => ['/tmp/php654321', '', '/tmp/php345678'],
                'size' => [1024000, 0, 512000],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_OK],
            ],
        ];

        $factory->fromArrays(
            post: [],
            files: $files
        );
    }

    public function test_file_array_parsing_with_deep_nesting(): void
    {
        $this->expectException(UploadException::class);

        $factory = new PostFactory();

        $files = [
            'attachments' => [
                'name' => [['document.pdf', 'image.png'], 'archive.zip'],
                'tmp_name' => [['/tmp/php654321', '/tmp/php789012'], '/tmp/php345678'],
                'size' => [[1024000, 2048000], 512000],
                'error' => [[UPLOAD_ERR_OK, UPLOAD_ERR_OK], UPLOAD_ERR_OK],
            ],
        ];

        $factory->fromArrays(
            post: [],
            files: $files
        );
    }
}
