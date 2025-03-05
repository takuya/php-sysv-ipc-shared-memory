<?php

namespace Tests\Units\SysV;

use Tests\TestCase;
use Takuya\SysV\IPCSharedMem;
use function Takuya\Helpers\str_rand;

class ShareWithInterProcessTest extends TestCase {
  
  public function test_sysv_inter_process_forked() {
    $key = rand(1, 100);
    $name = str_rand(10);
    $msg = str_rand(10);
    if (($pid = pcntl_fork())===false){
      throw new \Exception('fork failed');
    }
    if ( $pid===0 ){
      $shm = new IPCSharedMem($name);
      $shm->put($msg);
      $shm->detach();
      exit(0);
  
    }
    $shm = new IPCSharedMem($name);
    while($shm->isEmpty()){
      usleep(100);
    }
    $ret = $shm->get();
    $shm->erase();
    $empty  = $shm->isEmpty();
    pcntl_waitpid($pid,$st);
    $shm->destroy();
    $this->assertEquals($msg,$ret);
    $this->assertTrue($empty);
  }
}