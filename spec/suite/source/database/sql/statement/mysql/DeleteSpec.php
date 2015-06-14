<?php
namespace chaos\spec\suite\source\database\sql\statement;

use chaos\SourceException;
use chaos\source\database\sql\Sql;
use kahlan\plugin\Stub;

describe("Delete", function() {

    beforeEach(function() {
        $this->adapter = box('chaos.spec')->get('source.database.mysql');
        $this->delete = $this->adapter->sql()->statement('delete');
    });

    describe("->lowPriority()", function() {

        it("sets the `LOW_PRIORITY` flag", function() {

            $this->delete->lowPriority()->from('table');
            expect($this->delete->toString())->toBe('DELETE LOW_PRIORITY FROM `table`');

        });

    });

    describe("->ignore()", function() {

        it("sets the `IGNORE` flag", function() {

            $this->delete->ignore()->from('table');
            expect($this->delete->toString())->toBe('DELETE IGNORE FROM `table`');

        });

    });

    describe("->quick()", function() {

        it("sets the `QUICK` flag", function() {

            $this->delete->quick()->from('table');
            expect($this->delete->toString())->toBe('DELETE QUICK FROM `table`');

        });

    });

});