<?php

use Entity\File;

class FindTester extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $file = new File();
        $file->DB()->execute('DELETE FROM Files');

        $file = new File();
        $file->name = 'Test';
        $file->filename = 'Test';
        $file->file_group = 'Test';
        $file->Save();

        $this->assertEquals('', $file->ErrorMsg(), 'Error message should be empty');

        for ($i = 0; $i < 10; $i++) {
            $file = new File();
            $file->name = 'Test' . $i;
            $file->filename = 'Test' . $i;
            $file->file_group = 'Test' . $i;
            $file->Save();
        }

        $this->assertEquals('', $file->ErrorMsg(), 'Error message should be empty');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testLoad()
    {
        $file = new File();
        $file->Load('name = ?', ['Test']);
        $this->assertEquals('Test', $file->name, '$file->name should be Test');
        $this->assertEquals('Test', $file->file_group, '$file->file_group should be Test');
    }

    public function testFind()
    {
        $file1 = new File();
        $files = $file1->Find('name = ?', ['Test1']);
        $file1 = $files[0];
        $this->assertEquals('Test1', $file1->name, '$file->name should be Test1');
        $this->assertEquals('Test1', $file1->file_group, '$file->file_group should be Test1');

        $this->assertEquals(11, count($file1->Find('name LIKE ?', ['Test%'])), 'Should return 11 records');
        $this->assertEquals(11, count($file1->Find()), 'Should return 11 records');
        $this->assertEquals(5, count($file1->Find('1=1 LIMIT 5')), 'Should return 5 records');
    }

    public function testCount()
    {

        $file1 = new File();
        $this->assertEquals(11, $file1->Count('name LIKE ?', ['Test%']), 'Should return 11 records');
    }

    public function testUpdate() {
        $file = new File();
        $file->Load('name = ?', ['Test']);
        $this->assertEquals('Test', $file->file_group, '$file->file_group should be Test');
        $file->file_group = 'New Group';
        $file->Save();

        $updatedFile = new File();
        $updatedFile->Load('name = ?', ['Test']);
        $this->assertEquals('New Group', $updatedFile->file_group);
    }

    public function testDelete() {
        $file = new File();
        $file->Load('name = ?', ['Test']);
        $file->Delete();

        $file = new File();
        $file->Load('name = ?', ['Test']);
        $this->assertEquals(NULL, $file->id);

        $this->assertEquals(10, $file->Count('name LIKE ?', ['Test%']));
    }
}