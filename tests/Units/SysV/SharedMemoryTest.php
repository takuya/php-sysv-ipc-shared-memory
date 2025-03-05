<?php

namespace Tests\Units\SysV;

use Tests\TestCase;
use Takuya\SysV\IPCSharedMem;
use function Takuya\Helpers\str_rand;

class SharedMemoryTest extends TestCase {
  
  protected int $shm_cnt;
  protected int $sem_cnt;
  
  protected function setUp():void {
    parent::setUp();
    $this->shm_cnt = `ipcs -m | wc -l `;
    $this->sem_cnt = `ipcs -s | wc -l `;
  }
  
  protected function tearDown():void {
    parent::tearDown();
    $this->assertEquals($this->shm_cnt, `ipcs -m | wc -l `);
    $this->assertEquals($this->sem_cnt, `ipcs -s | wc -l `);
  }
  
  public function test_sysv_shared_memory_simple_var() {
    $key = rand(1, 100);
    $str = str_rand(10);
    $shm = new IPCSharedMem(str_rand(10));
    $shm->put($str);
    $ret = $shm->get();
    $exists = $shm->isEmpty();
    $shm->erase();
    $empty = $shm->isEmpty();
    $shm->destroy();
    $this->assertFalse($exists);
    $this->assertTrue($empty);
    $this->assertEquals($str,$ret);
  }
  
  public function test_sysv_shared_memory_object_var() {
    $key = rand(1, 100);
    $obj = new \stdClass();
    $obj->name =  str_rand(10);
    $shm = new IPCSharedMem(str_rand(10));
    $shm->put($obj);
    $ret = $shm->get();
    $exists = $shm->isEmpty();
    $shm->erase();
    $empty = $shm->isEmpty();
    $shm->destroy();
    $this->assertFalse($exists);
    $this->assertTrue($empty);
    $this->assertEquals($obj,$ret);
  }
}