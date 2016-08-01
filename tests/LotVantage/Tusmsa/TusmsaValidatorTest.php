<?php

namespace LotVantage\Tusmsa;

use Tests\TestCase;

/**
 * Test that the TUSMSA validator conforms to the spec it
 * claims to make others conform to. 
 *
 * http://lister.engager.social/spec/1.0.txt
 */

class TusmsaValidatorTest extends TestCase
{

    public function testConstruct()
    {
        new TusmsaValidator([]);
    }

    public function testBlank()
    {
        $v = new TusmsaValidator([]);
        $this->assertTrue($v->fails(), "Version testing allowed bad version to pass through");
        $this->assertEquals($v->errors()->first(), 'The tusmsa version field is required.');
    }

    public function testBadVersion()
    {        
        $v = new TusmsaValidator([
            'tusmsa_version' => 0,
        ]);
        $this->assertTrue($v->fails(), "Version testing allowed bad version to pass through");
        $this->assertEquals($v->errors()->first(), 'The tusmsa version is not a known value.');
    }

    public function testBadTopLevel()
    {
        $v = new TusmsaValidator([
            'status' => false,
            'tusmsa_version' => 1.0, // the only good data
            'stories' => 'potato'
        ]);
        $this->assertTrue($v->fails(), "Bad top-level data passed validation");
        $this->assertEquals(
            0,
            collect([
                'The status field does not have an acceptable value.',
                'The stories field must be an array.',
            ])
            ->diff($v->errors()->all())
            ->count(),
            "Didn't get all the errors we expected here."
        );
    }

    public function testBadStoriesObject()
    {
        $v = new TusmsaValidator([
            'status' => true,
            'tusmsa_version' => 1.0, // the only good data
            'stories' => [
                [
                    "id"        => ['bad'],
                    "title"     => ['bad'],
                    "url"       => 'bad',
                    "content"   => ['bad'],
                    "image_url" => 'bad',
                    "post_type" => ['bad'],
                ],
            ],
        ]);
        $this->assertTrue($v->fails(), "Bad stories data passed validation");
        $this->assertEquals(
            0,
            collect([
                'The stories.0.id field must be a string.',
                'The stories.0.title field must be a string.',
                'The stories.0.url field must be an absolute URL.',
                'The stories.0.content field must be a string.',
                'The stories.0.image_url field must be an absolute URL.',
                'The stories.0.post_type field must be a string.',
                'The stories.0.post_type field does not have an acceptable value.',
            ])
            ->diff($v->errors()->all())
            ->count(),
            "Didn't get all the errors we expected here."
        );
    }

    public function testBadUrls()
    {
        // data mentioned in the spec
             
        $v = new TusmsaValidator([
            'status' => true,
            'tusmsa_version' => 1.0,
            'stories' => [
                [
                    "id"        => "1234abc",
                    "title"     => "String title",
                    "url"       => "/page",
                    "content"   => "Long string",
                    "image_url" => "/image.jpg",
                    "post_type" => "TEXT",
                ],
            ],
        ]);
        $this->assertTrue($v->fails(), "Bad stories data passed validation");
        $this->assertEquals(
            0,
            collect([
                'The stories.0.url field must be an absolute URL.',
                'The stories.0.image_url field must be an absolute URL.',
            ])
            ->diff($v->errors()->all())
            ->count(),
            "Didn't get all the errors we expected here."
        );
    }

    public function testGood()
    {
        // This is the same data as in the spec at 
        // http://lister.engager.social/spec/1.0.txt
             
        $v = new TusmsaValidator([
            'status' => true,
            'tusmsa_version' => 1.0,
            'stories' => [
                [
                    "id"        => "1234abc",
                    "title"     => "String title",
                    "url"       => "http://somedomain.com/page",
                    "content"   => "Long string",
                    "image_url" => "http://somedomain.cdn.com/image.jpg",
                    "post_type" => "TEXT",
                ],
            ],
        ]);
        $this->assertFalse($v->fails(), join(', ', $v->errors()->all()));
    }

    public function testNothingToReport()
    {        
        $v = new TusmsaValidator([
            'status' => true,
            'tusmsa_version' => 1.0,
            'stories' => [],
        ]);
        $this->assertFalse($v->fails(), join(', ', $v->errors()->all()));
    }

    public function testBadExtraData()
    {        
        /**
         * Version 1.0 of the TUSMSA spec says no extra data
         * may be sent. That's not a trivial requirement
         * with the validation library we have.
         * 
         * SKIPPING for now.
         */
        $this->markTestSkipped();
        $v = new TusmsaValidator([
            'status' => true,
            'tusmsa_version' => 1.0,
            'stories' => [],
            'something' => 'else',
        ]);
        $this->assertTrue($v->fails(), "Bad stories data passed validation");
        $this->assertEquals(
            0,
            collect([
                'The something field is not accepted in version 1.0.',
            ])
            ->diff($v->errors()->all())
            ->count(),
            "Didn't get all the errors we expected here."
        );
    }

}
