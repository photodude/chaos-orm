<?php
namespace chaos\spec\suite\model;

use stdClass;
use DateTime;
use InvalidArgumentException;
use kahlan\plugin\Stub;
use chaos\SourceException;
use chaos\model\Model;
use chaos\model\Schema;

describe("Model", function() {

    afterEach(function() {
        Model::reset();
    });

	describe("->__construct()", function() {

        it("loads the data", function() {

            $date = new DateTime('2014-10-26 00:25:15');

            $entity = new Model(['data' => [
                'title'   => 'Hello',
                'body'    => 'World',
                'created' => $date
            ]]);
            expect($entity->title)->toBe('Hello');
            expect($entity->body)->toBe('World');
            expect($entity->created)->toBe($date);
            expect($entity)->toHaveLength(3);

        });

    });

    describe("->exists()", function() {

        it("returns the exists value", function() {

            $entity = new Model(['exists' => true]);
            expect($entity->exists())->toBe(true);

        });

    });

    describe("->parent()", function() {

        it("sets a parent", function() {

            $parent = Stub::create();
            $entity = new Model();
            $entity->parent($parent);
            expect($entity->parent())->toBe($parent);

        });

        it("returns the parent", function() {

            $parent = Stub::create();
            $entity = new Model(['parent' => $parent]);
            expect($entity->parent())->toBe($parent);

        });

    });

    describe("->rootPath()", function() {

        it("returns the root path", function() {

            $entity = new Model(['rootPath' => 'items']);
            expect($entity->rootPath())->toBe('items');

        });

    });

    describe("->primaryKey()", function() {

        it("returns the entity's primary key value", function() {

            $entity = new Model(['data' => [
                'id'      => 123,
                'title'   => 'Hello',
                'body'    => 'World'
            ]]);
            expect($entity->primaryKey())->toBe(123);

        });

        it("throws an exception if the schema has no primary key defined", function() {
            $schema = new Schema(['primaryKey' => null]);

            Model::config(compact('schema'));

            $closure = function() {
                $entity = new Model(['data' => [
                    'id'      => 123,
                    'title'   => 'Hello',
                    'body'    => 'World'
                ]]);
                $entity->primaryKey();
            };
            expect($closure)->toThrow(new SourceException("No primary key has been defined for `chaos\model\Model`'s schema."));

        });

    });

    describe("->sync()", function() {

        it("syncs an entity to its persisted value", function() {

            $entity = new Model(['data' => [
                'loaded'   => 'loaded'
            ]]);
            $entity->modified = 'modified';

            expect($entity->exists())->toBe(false);
            expect($entity->primaryKey())->toBe(null);
            expect($entity->modified('loaded'))->toBe(false);
            expect($entity->modified('modified'))->toBe(true);

            $entity->sync(123, ['added' => 'added'], ['exists' => true]);

            expect($entity->exists())->toBe(true);
            expect($entity->primaryKey())->toBe(123);
            expect($entity->modified('loaded'))->toBe(false);
            expect($entity->modified('modified'))->toBe(false);
            expect($entity->modified('added'))->toBe(false);
            expect($entity->added)->toBe('added');

        });

        context("when there's no primary key", function() {

            it("syncs an entity to its persisted value", function() {

                $entity = new Model(['data' => [
                    'loaded'   => 'loaded'
                ]]);
                $entity->modified = 'modified';

                expect($entity->exists())->toBe(false);
                expect($entity->primaryKey())->toBe(null);
                expect($entity->modified('loaded'))->toBe(false);
                expect($entity->modified('modified'))->toBe(true);

                $entity->sync(null, ['added' => 'added'], ['exists' => true]);

                expect($entity->exists())->toBe(true);
                expect($entity->primaryKey())->toBe(null);
                expect($entity->modified('loaded'))->toBe(false);
                expect($entity->modified('modified'))->toBe(false);
                expect($entity->modified('added'))->toBe(false);
                expect($entity->added)->toBe('added');

            });

        });

    });

    describe("->set()", function() {

        it("sets an array of values", function() {

            $date = new DateTime('2014-10-26 00:25:15');

            $entity = new Model();
            $entity->set([
                'title'   => 'Hello',
                'body'    => 'World',
                'created' => $date
            ]);
            expect($entity->title)->toBe('Hello');
            expect($entity->body)->toBe('World');
            expect($entity->created)->toBe($date);
            expect($entity)->toHaveLength(3);

        });

    });

    describe("->__set()", function() {

        it("sets value", function() {

            $entity = new Model();
            $entity->hello = 'world';
            expect($entity->hello)->toBe('world');

        });

        it("sets a value using a dedicated method", function() {

            $entity = Stub::create([
                'extends' => 'chaos\model\Model',
                'methods' => ['setHello']
            ]);
            Stub::on($entity)->method('setHello', function($value = null) {
                return 'boy';
            });

            $entity->hello = 'world';
            expect($entity->hello)->toBe('boy');

        });

    });

    describe("->get()", function() {

        it("returns `null` for undefined fields", function() {

            $entity = new Model();
            expect($entity->foo)->toBe(null);

        });

        it("returns all raw datas with no parameter", function() {

            $date = time();
            $entity = new Model(['data' => [
                'title'   => 'Hello',
                'body'    => 'World',
                'created' => $date
            ]]);
            expect($entity->get())->toBe([
                'title'   => 'Hello',
                'body'    => 'World',
                'created' => $date
            ]);

        });

        it("gets a value using a dedicated method", function() {

            $entity = Stub::create([
                'extends' => 'chaos\model\Model',
                'methods' => ['getHello']
            ]);
            Stub::on($entity)->method('getHello', function($value = null) {
                return 'boy';
            });

            expect($entity->hello)->toBe('boy');

        });

    });

    describe("->__get()", function() {

        it("gets value", function() {

            $entity = new Model();
            $entity->hello = 'world';
            expect($entity->hello)->toBe('world');
        });

        it("throws an exception if the field name is not valid", function() {

           $closure = function() {
                $entity = new Model();
                $empty = '';
                $entity->{$empty};
            };
            expect($closure)->toThrow(new SourceException("Field name can't be empty."));

        });

    });

    describe("->loaded()", function() {

        it("returns `loaded` data", function() {

            $entity = new Model(['data' => [
                'title'   => 'Hello',
                'body'    => 'World'
            ]]);

            $entity->set([
                'title'   => 'Good Bye',
                'body'    => 'Folks'
            ]);

            expect($entity->loaded('title'))->toBe('Hello');
            expect($entity->loaded('body'))->toBe('World');

        });

        it("returns all `loaded` data with no parameter", function() {

            $entity = new Model(['data' => [
                'title'   => 'Hello',
                'body'    => 'World'
            ]]);

            $entity->set([
                'title'   => 'Good Bye',
                'body'    => 'Folks'
            ]);

            expect($entity->loaded())->toBe([
                'title'   => 'Hello',
                'body'    => 'World'
            ]);

        });

    });

    describe("->modified()", function() {

        it("returns a boolean indicating if a field has been modified", function() {

            $entity = new Model(['data' => [
                'loaded'   => 'loaded'
            ]]);

            expect($entity->modified('loaded'))->toBe(false);
            expect($entity->modified('modified'))->toBe(false);

            $entity->modified = 'modified';

            expect($entity->modified('loaded'))->toBe(false);
            expect($entity->modified('modified'))->toBe(true);

            $entity->loaded = 'modified';

            expect($entity->modified('loaded'))->toBe(true);

        });

        it("returns `false` if a field has been updated with a same scalar value", function() {

            $entity = new Model(['data' => [
                'loaded'   => 'loaded'
            ]]);

            $entity->loaded = 'loaded';

            expect($entity->modified('loaded'))->toBe(false);

        });

        it("returns `false` if a field has been updated with a similar object value", function() {

            $entity = new Model(['data' => [
                'loaded'   => (object) 'loaded'
            ]]);

            $entity->loaded = (object) 'loaded';

            expect($entity->modified('loaded'))->toBe(false);

        });

        it("delegates the job for values which has a `modified()` method", function() {

            $child = new Model(['data' => [
                'loaded'   => 'loaded'
            ]]);

            $entity = new Model(['data' => ['child' => $child]]);
            expect($entity->modified('child'))->toBe(false);

            $child->loaded = 'modified';
            expect($entity->modified('child'))->toBe(true);

        });

        it("returns all modified field names with no parameter", function() {

            $entity = new Model();

            $entity->modified1 = 'modified';
            $entity->modified2 = 'modified';

            expect($entity->modified())->toBe(['modified1', 'modified2']);

        });

    });

    describe("->offsetExists()", function() {

        it("returns true if a element exist", function() {

            $entity = new Model();
            $entity['field1'] = 'foo';
            $entity['field2'] = null;

            expect(isset($entity['field1']))->toBe(true);
            expect(isset($entity['field2']))->toBe(true);

        });

        it("returns false if a element doesn't exist", function() {

            $entity = new Model();
            expect(isset($entity['undefined']))->toBe(false);

        });

    });

    describe("->offsetSet/offsetGet()", function() {

        it("allows array access", function() {

            $entity = new Model();
            $entity['field1'] = 'foo';
            expect($entity['field1'])->toBe('foo');
            expect($entity)->toHaveLength(1);

        });

        it("sets at a specific key", function() {

            $entity = new Model();
            $entity['mykey'] = 'foo';
            expect($entity['mykey'])->toBe('foo');
            expect($entity)->toHaveLength(1);

        });

        it("throws an exception for invalid key", function() {
            $closure = function() {
                $entity = new Model();
                $entity[] = 'foo';
            };
            expect($closure)->toThrow(new SourceException("Field name can't be empty."));

        });

        context("when a model is defined", function() {

            beforeEach(function() {
                $model = $this->model = Stub::classname(['extends' => 'chaos\model\Model']);
            });

            afterEach(function() {
                $model = $this->model;
                Model::reset();
            });

            it("autoboxes setted data", function() {

                $model = $this->model;
                $child = Stub::classname(['extends' => 'chaos\model\Model']);

                $schema = new Schema();
                $schema->add('child', [
                    'type' => 'object',
                    'class' => $child
                ]);

                $model::config(compact('schema'));

                $entity = new $model();

                $entity['child'] = [
                    'id'      => 1,
                    'title'   => 'child record',
                    'enabled' => true
                ];
                $child = $entity['child'];
                expect($child)->toBeAnInstanceOf($child);
                expect($child->parent())->toBe($entity);
                expect($child->rootPath())->toBe('child');

            });

        });

    });

    describe("->offsetUnset()", function() {

        it("unsets items", function() {

            $data = [
                'id'      => 1,
                'title'   => 'test record',
                'body'    => 'test body',
                'enabled' => true
            ];

            $entity = new Model(compact('data'));
            unset($entity['body']);
            unset($entity['enabled']);

            expect($entity)->toHaveLength(2);
            expect($entity->data())->toBe([
                'id'      => 1,
                'title'   => 'test record'
            ]);

        });

        it("unsets all items in a foreach", function() {

            $data = [
                'field1' => 'Delete me',
                'field2' => 'Delete me'
            ];
            $entity = new Model(compact('data'));

            foreach ($entity as $i => $word) {
                unset($entity[$i]);
            }

            expect($entity->data())->toBe([]);

        });

        it("unsets last items in a foreach", function() {

            $data = [
                'field1' => 'Hello',
                'field2' => 'Hello again!',
                'field3' => 'Delete me'
            ];
            $entity = new Model(compact('data'));

            foreach ($entity as $i => $word) {
                if ($word === 'Delete me') {
                    unset($entity[$i]);
                }
            }

            expect($entity->data())->toBe([
                'field1' => 'Hello',
                'field2' => 'Hello again!'
            ]);

        });

        it("unsets first items in a foreach", function() {

            $data = [
                'field1' => 'Delete me',
                'field2' => 'Hello',
                'field3' => 'Hello again!'
            ];
            $entity = new Model(compact('data'));

            foreach ($entity as $i => $word) {
                if ($word === 'Delete me') {
                    unset($entity[$i]);
                }
            }

            expect($entity->data())->toBe([
                'field2' => 'Hello',
                'field3' => 'Hello again!'
            ]);

        });

        it("doesn't skip element in foreach", function() {

            $data = [
                'field1' => 'Delete me',
                'field2' => 'Hello',
                'field3' => 'Delete me',
                'field4' => 'Hello again!'
            ];
            $entity = new Model(compact('data'));

            $loop = 0;
            foreach ($entity as $i => $word) {
                if ($word === 'Delete me') {
                    unset($entity[$i]);
                }
                $loop++;
            }

            expect($loop)->toBe(4);

            expect($entity->data())->toBe([
                'field2' => 'Hello',
                'field4' => 'Hello again!'
            ]);

        });

    });

    describe("->key()", function() {

        it("returns current key", function() {

            $data = ['field' => 'value'];
            $entity = new Model(compact('data'));
            $value = $entity->key();
            expect($value)->toBe('field');

        });

        it("returns null if non valid", function() {

            $entity = new Model();
            $value = $entity->key();
            expect($value)->toBe(null);

        });

    });

    describe("->current()", function() {

        it("returns the current value", function() {

            $data = ['field' => 'value'];
            $entity = new Model(compact('data'));
            $value = $entity->current();
            expect($value)->toBe('value');

        });

    });

    describe("->next()", function() {

        it("returns the next value", function() {

            $data = [
                'field1' => 'value1',
                'field2' => 'value2'
            ];
            $entity = new Model(compact('data'));
            $value = $entity->next();
            expect($value)->toBe('value2');

        });

    });

    describe("->prev()", function() {

        it("navigates through collection", function() {

            $data = [
                'id'      => 1,
                'title'   => 'test record',
                'body'    => 'test body'
            ];
            $entity = new Model(compact('data'));

            $entity->rewind();
            expect($entity->next())->toBe('test record');
            expect($entity->next())->toBe('test body');
            expect($entity->next())->toBe(null);

            $entity->end();
            expect($entity->prev())->toBe('test record');
            expect($entity->prev())->toBe(1);
            expect($entity->prev())->toBe(null);

        });

    });

    describe("->first/rewind/end()", function() {

        it("returns respectively the first and the last item of the collection", function() {

            $data = [
                'id'      => 1,
                'title'   => 'test record',
                'body'    => 'test body'
            ];
            $entity = new Model(compact('data'));

            expect($entity->end())->toBe('test body');
            expect($entity->rewind())->toBe(1);
            expect($entity->end())->toBe('test body');
            expect($entity->first())->toBe(1);

        });

    });

    describe("->valid()", function() {

        it("returns true only when the collection is valid", function() {

            $entity = new Model();
            expect($entity->valid())->toBe(false);

            $data = [
                'id'      => 1,
                'title'   => 'test record',
                'body'    => 'test body'
            ];
            $entity = new Model(compact('data'));
            expect($entity->valid())->toBe(true);

        });

    });

    describe("->count()", function() {

        it("returns 0 on empty", function() {

            $entity = new Model();
            expect($entity)->toHaveLength(0);

        });

        it("returns the number of items in the collection", function() {

            $data = [
                'id'      => 1,
                'title'   => 'test record',
                'body'    => 'test body',
                'enabled' => true,
                'null'    => null,
                'onject'  => new stdClass()
            ];
            $entity = new Model(compact('data'));
            expect($entity)->toHaveLength(6);

        });

    });

    describe("->__toString()", function() {

        it("returns the title field", function() {

            $data = [
                'id'      => 1,
                'title'   => 'test record',
                'body'    => 'test body',
                'enabled' => true,
                'null'    => null,
                'onject'  => new stdClass()
            ];
            $entity = new Model(compact('data'));
            expect((string) $entity)->toBe('test record');

        });

    });

    describe("::schema()", function() {

        it("returns the model", function() {

            $entity = new Model();
            $schema = $entity->schema();
            expect($schema)->toBeAnInstanceOf('chaos\model\Schema');

        });

    });

    describe("::conventions()", function() {

        it("sets/gets a conventions", function() {

            $conventions = Stub::create();
            Model::conventions($conventions);
            expect(Model::conventions())->toBe($conventions);

        });

    });

    describe("::connection()", function() {

        it("sets/gets a connection", function() {

            $connection = Stub::create();
            Model::connection($connection);
            expect(Model::connection())->toBe($connection);

        });

    });

    describe("::find()", function() {

        it("throws an exception", function() {
            $closure = function() {
                Model::find();
            };
            expect($closure)->toThrow(new SourceException("The `find()` method is not supported by `chaos\model\Model`."));

        });

    });

    describe("::update()", function() {

        it("throws an exception", function() {
            $closure = function() {
                Model::update(['enabled' => true]);
            };
            expect($closure)->toThrow(new SourceException("The `update()` method is not supported by `chaos\model\Model`."));

        });

    });

    describe("::remove()", function() {

        it("throws an exception", function() {
            $closure = function() {
                Model::remove();
            };
            expect($closure)->toThrow(new SourceException("The `remove()` method is not supported by `chaos\model\Model`."));

        });

    });

    describe("->save()", function() {

        it("throws an exception", function() {
            $closure = function() {
                $entity = new Model();
                $entity->save();
            };
            expect($closure)->toThrow(new SourceException("The `save()` method is not supported by `chaos\model\Model`."));

        });

    });

    describe("->delete()", function() {

        it("throws an exception", function() {
            $closure = function() {
                $entity = new Model();
                $entity->delete();
            };
            expect($closure)->toThrow(new SourceException("The `delete()` method is not supported by `chaos\model\Model`."));

        });

    });

    describe("::reset()", function() {

        it("resets the class", function() {

            $schema = Stub::create();
            $connection = Stub::create();
            $conventions = Stub::create();

            Model::config(compact('schema', 'connection', 'conventions'));

            expect(Model::schema())->toBe($schema);
            expect(Model::connection())->toBe($connection);
            expect(Model::conventions())->toBe($conventions);

            Model::reset();

            expect(Model::connection())->toBe(null);
            expect(Model::schema())->not->toBe($schema);
            expect(Model::conventions())->not->toBe($conventions);

        });

    });

});