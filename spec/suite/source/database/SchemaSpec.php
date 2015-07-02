<?php
namespace chaos\spec\suite\database;

use set\Set;
use chaos\model\Model;
use chaos\source\database\Query;

use kahlan\plugin\Stub;
use chaos\spec\fixture\Fixtures;

$connections = [
    "MySQL" => box('chaos.spec')->get('source.database.mysql'),
    //"PgSql" => box('chaos.spec')->get('source.database.mysql')
];

foreach ($connections as $db => $connection) {

    describe("Schema[{$db}]", function() use ($connection) {

        beforeEach(function() use ($connection) {

            $this->connection = $connection;
            $this->fixtures = new Fixtures([
                'connection' => $this->connection,
                'fixtures'   => [
                    'gallery'        => 'chaos\spec\fixture\schema\Gallery',
                    'gallery_detail' => 'chaos\spec\fixture\schema\GalleryDetail',
                    'image'          => 'chaos\spec\fixture\schema\Image',
                    'image_tag'      => 'chaos\spec\fixture\schema\ImageTag',
                    'tag'            => 'chaos\spec\fixture\schema\Tag'
                ]
            ]);

            $this->fixtures->populate('gallery', ['create']);
            $this->fixtures->populate('gallery_detail', ['create']);
            $this->fixtures->populate('image', ['create']);
            $this->fixtures->populate('image_tag', ['create']);
            $this->fixtures->populate('tag', ['create']);

            $this->gallery = $this->fixtures->get('gallery')->model();
            $this->galleryDetail = $this->fixtures->get('gallery_detail')->model();
            $this->image = $this->fixtures->get('image')->model();
            $this->image_tag = $this->fixtures->get('image_tag')->model();
            $this->tag = $this->fixtures->get('tag')->model();

        });

        afterEach(function() {
            $this->fixtures->drop();
        });

        context("with all data populated", function() {

            beforeEach(function() {

                $this->fixtures->populate('gallery', ['records']);
                $this->fixtures->populate('gallery_detail', ['records']);
                $this->fixtures->populate('image', ['records']);
                $this->fixtures->populate('image_tag', ['records']);
                $this->fixtures->populate('tag', ['records']);

            });

            it("embeds a hasMany relationship", function() {

                $model = $this->gallery;
                $schema = $model::schema();
                $galleries = $model::all(['order' => 'id']);
                $schema->embed($galleries, ['images']);

                foreach ($galleries as $gallery) {
                    foreach ($gallery->images as $image) {
                        expect($gallery->id)->toBe($image->gallery_id);
                    }
                }

            });

            it("embeds a belongsTo relationship", function() {

                $model = $this->image;
                $schema = $model::schema();
                $images = $model::all(['order' => 'id']);
                $schema->embed($images, ['gallery']);

                foreach ($images as $image) {
                    expect($image->gallery_id)->toBe($image->gallery->id);
                }

            });

            it("embeds a hasOne relationship", function() {

                $model = $this->gallery;
                $schema = $model::schema();
                $galleries = $model::all(['order' => 'id']);
                $schema->embed($galleries, ['detail', 'images']);

                foreach ($galleries as $gallery) {
                    expect($gallery->id)->toBe($gallery->detail->gallery_id);
                }

            });

            it("embeds a hasManyTrough relationship", function() {

                $model = $this->image;
                $schema = $model::schema();
                $images = $model::all(['order' => 'id']);
                $schema->embed($images, ['tags']);

                foreach ($images as $image) {
                    foreach ($image->images_tags as $index => $image_tag) {
                        expect($image_tag->tag)->toBe($image->tags[$index]);
                    }
                }
            });

            it("embeds nested hasManyTrough relationship", function() {

                $model = $this->image;
                $schema = $model::schema();
                $images = $model::all(['order' => 'id']);
                $schema->embed($images, ['tags.images']);

                foreach ($images as $image) {
                    foreach ($image->images_tags as $index => $image_tag) {
                        expect($image_tag->tag)->toBe($image->tags[$index]);

                        foreach ($image_tag->tag->images_tags as $index2 => $image_tag2) {
                            expect($image_tag2->image)->toBe($image_tag->tag->images[$index2]);
                        }
                    }
                }
            });

        });

        fit("save a nested entity", function() {

            $data = [
                'name' => 'Foo Gallery',
                'images' => [
                    [
                        'name' => 'someimage.png',
                        'title' => 'Image1 Title',
                        'tags' => [
                            ['name' => 'tag1'],
                            ['name' => 'tag2'],
                            ['name' => 'tag3']
                        ]
                    ],
                    [
                        'name' => 'anotherImage.jpg',
                        'title' => 'Our Vacation',
                        'tags' => [
                            ['name' => 'tag4'],
                            ['name' => 'tag5']
                        ]
                    ],
                    [
                        'name' => 'me.bmp',
                        'title' => 'Me.',
                        'tags' => []
                    ]
                ]
            ];

            $model = $this->gallery;
            $gallery = $model::create($data);
            expect($gallery->save(['with' => true]))->toBe(true);

            $result = $model::find()->with(['images.tags'])->all();
            print_r($result->data());

        });

    });

};
