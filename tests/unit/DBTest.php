<?php namespace unit;
use DB;

class DBTest extends \Codeception\Test\Unit {

    /** @var \UnitTester */
    protected $tester;

    protected function _before() { }

    protected function _after() { }

    public function testConnection() {
        $main = DB::conn();
        $this->assertInstanceOf(\PDO::class, $main);

        $_SESSION['user_data_source'] = 'tests_extra';
        $user = DB::conn(DB::DB_LOCALHOST, true);
        $this->assertInstanceOf(\PDO::class, $user);

        $main_db = $main->query('select database()')->fetchColumn();
        $user_db = $user->query('select database()')->fetchColumn();
        $this->assertNotEquals($main_db, $user_db);
    }

    //TODO: replace this with factory
    private function randomBodyPart($create = false, $removeCreated = true) { //funny function name + simple table
        $fields = [
          'bodyparts_uuid' => faker()->unique()->asciify(str_repeat('*', 15)),
          'code'           => faker()->unique()->randomNumber,
          'description'    => ''
        ];
        if ($create) {
            $id = $this->tester->haveInDatabase('cse_bodyparts', $fields);
            if ($removeCreated) {
                $this->tester->scheduleRemovalFromDatabase('cse_bodyparts', $id, 'bodyparts_id');
            }
            return $fields + ['bodyparts_id' => $id];
        } else {
            return $fields;
        }
    }

    public function testRun() {
        $id = [];
        foreach ([0,1] as $i) {
            $id[$i] = $this->randomBodyPart(true)['bodyparts_id'];
        }

        $run = fn ($clause, $args) => DB::run("SELECT COUNT(*) FROM cse_bodyparts WHERE bodyparts_id $clause", $args)->fetchColumn();

        $this->assertEquals(1, $run('= ?', $id[0]), 'single param failed');
        $this->assertEquals(1, $run('= ?', [$id[0]]), 'single param array failed');
        $this->assertEquals(2, $run('IN (?,?)', [$id[0], $id[1]]), 'indexed array failed');
        $this->assertEquals(2, $run('IN (:id0,:id1)', ['id0' => $id[0], 'id1' => $id[1]]), 'assoc array failed');
        //can't mix indexed and associative arrays on parameterized queries, so we stop here :)
    }

    public function testRunOrX() {
        $this->markTestIncomplete('Cannot test dies :(');
        $bad_query = 'SELECT * FROM bad_table';
        $this->assertStringContainsString('bad_table', DB::runOrDie($bad_query));

        $result = DB::runOrApiError($bad_query);
        $this->assertJson($result);
        $this->assertStringContainsString('bad_table', $result);
    }

    /**
     * @covers DB::lastInsertId() In case of succesful inserts
     */
    public function testInsert() {
        $fields = $this->randomBodyPart();
        $this->tester->dontSeeInDatabase('cse_bodyparts', $fields); //sanity check
        $id = DB::insert('cse_bodyparts', $fields);
        $this->tester->seeInDatabase('cse_bodyparts', $fields);
        $found_id = $this->tester->grabColumnFromDatabase('cse_bodyparts', 'bodyparts_id', $fields)[0];
        $this->assertEquals($found_id, $id);
    }

    /**
     * In the test env it will just discard the error, but we checked that it's properly generated at {@link testRunOrX}
     * @covers DB::lastInsertId() In case of failed inserts
     */
    public function testInsertOrDie() {
        $this->markTestIncomplete('Cannot test dies :(');
        $id = DB::insertOrDie('cse_bodyparts', []);
        $this->assertEmpty($id);
    }

    /**
     * @depends testInsert
     */
    public function testLastInsertIdClearsOnErrors() {
        $id = DB::insert('cse_bodyparts', $this->randomBodyPart());
        $this->assertGreaterThan(0, $id);
        $this->markTestIncomplete('Cannot test dies :(');
        $null = DB::insertOrDie('cse_bodyparts', []);
        $this->assertEmpty($null);
    }

    public function testSelect() {
        $fields = $this->randomBodyPart(true);
        $query  = 'SELECT * FROM cse_bodyparts WHERE bodyparts_id = ?';

        $obj = DB::select($query, $fields['bodyparts_id'])[0];
        $this->assertIsObject($obj);

        $array = DB::select($query, $fields['bodyparts_id'], \PDO::FETCH_ASSOC)[0];
        $this->assertEquals($fields, $array);
    }

    public function testUpdate() {
        $fields = $this->randomBodyPart(true);
        $this->tester->seeInDatabase('cse_bodyparts', $fields); //sanity check

        $updated = ['description' => faker()->sentence];
        $rows = DB::update('cse_bodyparts', $updated, ['bodyparts_id' => $fields['bodyparts_id']]);
        $this->assertEquals(1, $rows);
        $this->tester->seeInDatabase('cse_bodyparts', $updated + $fields);
    }

    public function testUpdateOrDie() {
        $this->markTestIncomplete('Cannot test dies :(');
        $fields = $this->randomBodyPart(true);
        $this->tester->seeInDatabase('cse_bodyparts', $fields); //sanity check

        $rows = DB::updateOrDie('cse_bodyparts', ['wrong_field' => null], ['bodyparts_id' => $fields['bodyparts_id']]);
        $this->assertEquals(0, $rows);
        $this->tester->seeInDatabase('cse_bodyparts', $fields); //make sure it didn't change
    }

    public function testDelete() {
        $fields = $this->randomBodyPart(true, false);
        $clause = array_intersect_key($fields, ['bodyparts_id' => null]); //extracting the id
        $this->tester->seeInDatabase('cse_bodyparts', $clause); //sanity check

        $rows = DB::delete('cse_bodyparts', $clause);
        $this->assertEquals(1, $rows);
        $this->tester->dontSeeInDatabase('cse_bodyparts', $clause);
    }

    public function testDeleteOrDie() {
        $this->markTestIncomplete('Cannot test dies :(');
        $fields = $this->randomBodyPart(true);
        $this->tester->seeInDatabase('cse_bodyparts', ['bodyparts_id' => $fields['bodyparts_id']]); //sanity check

        $rows = DB::deleteOrError('cse_bodyparts', ['wrong_id' => null], false);
        $this->assertEquals(0, $rows);
        $this->tester->seeInDatabase('cse_bodyparts', $fields); //doubly sure it wasn't deleted
    }

    public function testDeleteWhereIn() {
        $parts = $this->tester->haveMultiple('cse_bodyparts', 3);
        $ids = array_column($parts, 'bodyparts_id');

        $rows = DB::delete('cse_bodyparts', ['bodyparts_id' => $ids]);
        self::assertEquals(3, $rows);
        $this->tester->dontSeeInDatabase('cse_bodyparts', ['bodyparts_id' => $ids[0]]);
        $this->tester->dontSeeInDatabase('cse_bodyparts', ['bodyparts_id' => $ids[1]]);
        $this->tester->dontSeeInDatabase('cse_bodyparts', ['bodyparts_id' => $ids[2]]);
    }

    public function testEmptyWhereUpdate() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty');
        DB::update('cse_bodyparts', ['bodyparts_id' => 0], []);
    }

    public function testEmptyWhereDelete() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty');
        DB::delete('cse_bodyparts', []);
    }

    public function testBadWhereUpdate() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be');
        DB::update('cse_bodyparts', ['bodyparts_id' => 0], [1 => 1]);
    }

    public function testBadWhereDelete() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be');
        DB::delete('cse_bodyparts', [1 => 1]);
    }
}
