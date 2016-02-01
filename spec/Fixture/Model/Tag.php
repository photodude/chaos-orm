<?php
namespace Chaos\Spec\Fixture\Model;

class Tag extends \Chaos\Model
{
    protected static function _define($schema)
    {
        $schema->set('id', ['type' => 'serial']);
        $schema->set('name', ['type' => 'string', 'length' => 50]);

        $schema->hasMany('images_tags', ImageTag::class, [
            'key' => ['id' => 'tag_id']
        ]);

        $schema->hasManyThrough('images', 'images_tags', 'image');
    }

}