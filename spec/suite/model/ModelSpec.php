<?php
namespace chaos\spec\suite\model;

use stdClass;
use DateTime;
use InvalidArgumentException;
use chaos\SourceException;
use chaos\model\Model;
use chaos\model\Schema;

use kahlan\plugin\Stub;

describe("Model", function() {

    before(function() {
        $this->model = Stub::classname(['extends' => Model::class]);
    });

    afterEach(function() {
        $model = $this->model;
        $model::config(); // (acts like a reset)
    });

	describe("->__construct()", function() {

        it("loads the data", function() {

            $date = new DateTime('2014-10-26 00:25:15');

            $model = $this->model;
            $entity = new $model(['data' => [
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

            $model = $this->model;
            $entity = $model::create([], ['exists' => true, 'partial' => false]);
            expect($entity->exists())->toBe(true);

        });

    });

    describe("->parent()", function() {

        it("sets a parent", function() {

            $parent = Stub::create();
            $model = $this->model;
            $entity = $model::create();
            $entity->parent($parent);
            expect($entity->parent())->toBe($parent);

        });

        it("returns the parent", function() {

            $parent = Stub::create();
            $model = $this->model;
            $entity = $model::create([], ['parent' => $parent]);
            expect($entity->parent())->toBe($parent);

        });

    });

    describe("->rootPath()", function() {

        it("returns the root path", function() {

            $model = $this->model;
            $entity = $model::create([], ['rootPath' => 'items']);
            expect($entity->rootPath())->toBe('items');

        });

    });

    describe("->primaryKey()", function() {

        it("returns the entity's primary key value", function() {

            $model = $this->model;
            $entity = $model::create([
                'id'      => 123,
                'title'   => 'Hello',
                'body'    => 'World'
            ]);
            expect($entity->primaryKey())->toBe(123);

        });

        it("throws an exception if the schema has no primary key defined", function() {
            $schema = new Schema(['primaryKey' => null]);

            $model = $this->model;
            $model::config(compact('schema'));

            $closure = function() {
                $model = $this->model;
                $entity = $model::create([
                    'id'      => 123,
                    'title'   => 'Hello',
                    'body'    => 'World'
                ]);
                $entity->primaryKey();
            };
            expect($closure)->toThrow(new SourceException("No primary key has been defined for `{$model}`'s schema."));

        });

    });

    describe("->sync()", function() {

        it("syncs an entity to its persisted value", function() {

            $model = $this->model;
            $entity = $model::create();
            $entity->modified = 'modified';

            expect($entity->exists())->toBe(false);
            expect($entity->primaryKey())->toBe(null);
            expect($entity->modified('modified'))->toBe(true);

            $entity->sync(123, ['added' => 'added'], ['exists' => true]);

            expect($entity->exists())->toBe(true);
            expect($entity->primaryKey())->toBe(123);
            expect($entity->modified('modified'))->toBe(false);
            expect($entity->modified('added'))->toBe(false);
            expect($entity->added)->toBe('added');

        });

        context("when there's no primary key", function() {

            it("syncs an entity to its persisted value", function() {

                $model = $this->model;
                $entity = $model::create();
                $entity->modified = 'modified';

                expect($entity->exists())->toBe(false);
                expect($entity->primaryKey())->toBe(null);
                expect($entity->modified('modified'))->toBe(true);

                $entity->sync(null, ['added' => 'added'], ['exists' => true]);

                expect($entity->exists())->toBe(true);
                expect($entity->primaryKey())->toBe(null);
                expect($entity->modified('modified'))->toBe(false);
                expect($entity->modified('added'))->toBe(false);
                expect($entity->added)->toBe('added');

            });

        });

    });

    describe("->set()", function() {

        it("sets an array of values", function() {

            $date = new DateTime('2014-10-26 00:25:15');

            $model = $this->model;
            $entity = $model::create();
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

            $model = $this->model;
            $entity = $model::create();
            $entity->hello = 'world';
            expect($entity->hello)->toBe('world');

        });

        it("sets a value using a dedicated method", function() {

            $entity = Stub::create([
                'extends' => $this->model,
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

            $model = $this->model;
            $entity = $model::create();
            expect($entity->foo)->toBe(null);

        });

        it("returns all raw datas with no parameter", function() {

            $date = time();
            $model = $this->model;
            $entity = $model::create([
                'title'   => 'Hello',
                'body'    => 'World',
                'created' => $date
            ]);
            expect($entity->get())->toBe([
                'title'   => 'Hello',
                'body'    => 'World',
                'created' => $date
            ]);

        });

        it("gets a value using a dedicated method", function() {

            $entity = Stub::create([
                'extends' => $this->model,
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

            $model = $this->model;
            $entity = $model::create();
            $entity->hello = 'world';
            expect($entity->hello)->toBe('world');
        });

        it("throws an exception if the field name is not valid", function() {

           $closure = function() {
                $model = $this->model;
                $entity = $model::create();
                $empty = '';
                $entity->{$empty};
            };
            expect($closure)->toThrow(new SourceException("Field name can't be empty."));

        });

    });

    describe("->persisted()", function() {

        it("returns persisted data", function() {

            $model = $this->model;
            Stub::on($model)->method('::id', function() {
                return static::create([
                    'id'      => '1',
                    'title'   => 'Hello',
                    'body'    => 'World'
                ]);
            });

            $entity = $model::create([
                'id' => 1,
                'title'   => 'Good Bye',
                'body'    => 'Folks'
            ], ['exists' => true]);


            expect($entity->persisted('title'))->toBe('Hello');
            expect($entity->persisted('body'))->toBe('World');

            expect($entity->title)->toBe('Good Bye');
            expect($entity->body)->toBe('Folks');

            expect($entity->modified('title'))->toBe(true);
            expect($entity->modified('body'))->toBe(true);

        });

        it("returns all persisted data with no parameter", function() {

            $model = $this->model;
            Stub::on($model)->method('::id', function() {
                return static::create([
                    'id'      => 1,
                    'title'   => 'Hello',
                    'body'    => 'World'
                ]);
            });

            $entity = $model::create([
                'id' => 1,
                'title'   => 'Good Bye',
                'body'    => 'Folks'
            ], ['exists' => true]);

            expect($entity->persisted())->toBe([
                'id' => 1,
                'title'   => 'Hello',
                'body'    => 'World'
            ]);

        });

    });

    describe("->modified()", function() {

        it("returns a boolean indicating if a field has been modified", function() {

            $model = $this->model;
            $entity = $model::create(['title' => 'original'], ['exists' => true, 'partial' => false]);

            expect($entity->modified('title'))->toBe(false);

            $entity->title = 'modified';
            expect($entity->modified('title'))->toBe(true);

        });

        it("returns `false` if a field has been updated with a same scalar value", function() {

            $model = $this->model;
            $entity = $model::create(['title' => 'original'], ['exists' => true, 'partial' => false]);

            expect($entity->modified('title'))->toBe(false);

            $entity->title = 'original';
            expect($entity->modified('title'))->toBe(false);

        });

        it("returns `false` if a field has been updated with a similar object value", function() {

            $model = $this->model;
            $entity = $model::create(['body'  => (object) 'body'], ['exists' => true, 'partial' => false]);

            expect($entity->modified('body'))->toBe(false);

            $entity->title = (object) 'body';
            expect($entity->modified('body'))->toBe(false);

        });

        it("delegates the job for values which has a `modified()` method", function() {

            $model = $this->model;
            $child = Stub::classname(['extends' => $this->model]);
            $model::schema()->set('child', [
                'type' => 'object',
                'to'   => $child
            ]);

            $subentity = $child::create(['field' => 'value'], ['exists' => true, 'partial' => false]);

            $entity = $model::create(['child' => $subentity], ['exists' => true, 'partial' => false]);

            expect($entity->modified())->toBe(false);

            $entity->child->field = 'modified';
            expect($entity->modified())->toBe(true);

        });

        it("returns all modified field names with no parameter", function() {

            $model = $this->model;
            $entity = $model::create();

            $entity->modified1 = 'modified';
            $entity->modified2 = 'modified';

            expect($entity->modified())->toBe(true);

        });

    });

    describe("->offsetExists()", function() {

        it("returns true if a element exist", function() {

            $model = $this->model;
            $entity = $model::create();
            $entity['field1'] = 'foo';
            $entity['field2'] = null;

            expect(isset($entity['field1']))->toBe(true);
            expect(isset($entity['field2']))->toBe(true);

        });

        it("returns false if a element doesn't exist", function() {

            $model = $this->model;
            $entity = $model::create();
            expect(isset($entity['undefined']))->toBe(false);

        });

    });

    describe("->offsetSet/offsetGet()", function() {

        it("allows array access", function() {

            $model = $this->model;
            $entity = $model::create();
            $entity['field1'] = 'foo';
            expect($entity['field1'])->toBe('foo');
            expect($entity)->toHaveLength(1);

        });

        it("sets at a specific key", function() {

            $model = $this->model;
            $entity = $model::create();
            $entity['mykey'] = 'foo';
            expect($entity['mykey'])->toBe('foo');
            expect($entity)->toHaveLength(1);

        });

        it("throws an exception for invalid key", function() {
            $closure = function() {
                $model = $this->model;
                $entity = $model::create();
                $entity[] = 'foo';
            };
            expect($closure)->toThrow(new SourceException("Field name can't be empty."));

        });

        context("when a model is defined", function() {

            beforeEach(function() {
                $this->model = Stub::classname(['extends' => $this->model]);
            });

            it("autoboxes setted data", function() {

                $model = $this->model;
                $child = Stub::classname(['extends' => $this->model]);

                $schema = new Schema(['model' => $model]);
                $schema->set('child', [
                    'type' => 'object',
                    'class' => $child
                ]);

                $model::config(compact('schema'));

                $entity = $model::create();

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

            $model = $this->model;
            $entity = $model::create($data);
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

            $model = $this->model;
            $entity = $model::create($data);

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

            $model = $this->model;
            $entity = $model::create($data);

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

            $model = $this->model;
            $entity = $model::create($data);

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

            $model = $this->model;
            $entity = $model::create($data);

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
            $model = $this->model;
            $entity = $model::create($data);
            $value = $entity->key();
            expect($value)->toBe('field');

        });

        it("returns null if non valid", function() {

            $model = $this->model;
            $entity = $model::create();
            $value = $entity->key();
            expect($value)->toBe(null);

        });

    });

    describe("->current()", function() {

        it("returns the current value", function() {

            $data = ['field' => 'value'];
            $model = $this->model;
            $entity = $model::create($data);
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

            $model = $this->model;
            $entity = $model::create($data);
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

            $model = $this->model;
            $entity = $model::create($data);

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

    describe("->rewind/end()", function() {

        it("returns respectively the first and the last item of the collection", function() {

            $data = [
                'id'      => 1,
                'title'   => 'test record',
                'body'    => 'test body'
            ];

            $model = $this->model;
            $entity = $model::create($data);

            expect($entity->end())->toBe('test body');
            expect($entity->rewind())->toBe(1);
            expect($entity->end())->toBe('test body');
            expect($entity->rewind())->toBe(1);

        });

    });

    describe("->valid()", function() {

        it("returns true only when the collection is valid", function() {

            $model = $this->model;
            $entity = $model::create();
            expect($entity->valid())->toBe(false);

            $data = [
                'id'      => 1,
                'title'   => 'test record',
                'body'    => 'test body'
            ];
            $entity = $model::create($data);
            expect($entity->valid())->toBe(true);

        });

    });

    describe("->count()", function() {

        it("returns 0 on empty", function() {

            $model = $this->model;
            $entity = $model::create();
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

            $model = $this->model;
            $entity = $model::create($data);
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

            $model = $this->model;
            $entity = $model::create($data);
            expect((string) $entity)->toBe('test record');

        });

    });

    describe("::schema()", function() {

        it("returns the model", function() {

            $model = $this->model;
            $schema = $model::schema();
            expect($schema)->toBeAnInstanceOf('chaos\model\Schema');
            expect($schema)->toBe($model::schema());

        });

    });

    describe("::conventions()", function() {

        it("sets/gets a conventions", function() {

            $conventions = Stub::create();
            $model = $this->model;
            $model::conventions($conventions);
            expect($model::conventions())->toBe($conventions);

        });

    });

    describe("::connection()", function() {

        it("sets/gets a connection", function() {

            $connection = Stub::create();
            $model = $this->model;
            $model::connection($connection);
            expect($model::connection())->toBe($connection);

        });

    });

});