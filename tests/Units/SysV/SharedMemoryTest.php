<?php

namespace Tests\Units\SysV;

use stdClass;
use Tests\TestCase;
use Takuya\SysV\IPCSharedMem;
use function Takuya\Helpers\str_rand;

class SharedMemoryTest extends TestCase {
  
  public function test_sysv_shared_memory_simple_var() {
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
    $this->assertEquals($str, $ret);
  }
  
  public function test_sysv_shared_memory_object_var() {
    $obj = new stdClass();
    $obj->name = str_rand(10);
    $shm = new IPCSharedMem(str_rand(10));
    $shm->put($obj);
    $ret = $shm->get();
    $exists = $shm->isEmpty();
    $shm->erase();
    $empty = $shm->isEmpty();
    $shm->destroy();
    $this->assertFalse($exists);
    $this->assertTrue($empty);
    $this->assertEquals($obj, $ret);
  }
  
  public function test_sysv_shared_memory_get_from_empty() {
    $shm = new IPCSharedMem(str_rand(10));
    $ret = $shm->get();
    $shm->destroy();
    $this->assertNull($ret);
  }
  
  public function test_sysv_shared_memory_put_null() {
    $shm = new IPCSharedMem(str_rand(10));
    $ret[] = $shm->put(null);
    $ret[] = $shm->get();
    $shm->destroy();
    $this->assertEquals([true, null], $ret);
  }
  
  public function test_sysv_shared_memory_attach_destroy_multiple() {
    $shm = new IPCSharedMem(str_rand(10));
    $ret[] = $shm->attach();
    $ret[] = $shm->attach();
    $ret[] = $shm->destroy();
    $ret[] = $shm->destroy();
    $ret[] = $shm->destroy();
    $this->assertEquals([true,true,true,true,true],$ret);
  }
  public function test_sysv_shared_memory_get_after_destroy() {
    $shm = new IPCSharedMem(str_rand(10));
    $ret[] = $shm->destroy();
    $ret[] = $shm->get();
    $this->assertEquals([true,null],$ret);
  }
  public function test_sysv_shared_memory_put_after_destroy() {
    $msg = str_rand();
    $shm = new IPCSharedMem(str_rand(10));
    $ret[] = $shm->destroy();// shm_remove() を呼び出しても、デストラクタされない限り残るはず。
    $ret[] = $shm->put($msg);// SysvSharedMemory reference keep IPC.
    $ret[] = $shm->get();
    $this->assertEquals([true,true,$msg],$ret);
  }
}