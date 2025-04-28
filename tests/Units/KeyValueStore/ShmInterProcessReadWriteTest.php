<?php

namespace Tests\Units\KeyValueStore;

use Tests\TestCase;
use Takuya\SysV\IPCShmKeyStore;
use function Takuya\Helpers\str_rand;
use function Takuya\Helpers\child_fork;

class ShmInterProcessReadWriteTest extends TestCase {
  
  /**
   * @throws \Exception
   */
  public function test_shm_inter_process_rw() {
    
    $name = str_rand(10);
    $msg = str_rand(10);
    $size = 150;
    $cpids = [];
    // fork
    foreach (range(0,30) as $iter){
      $cpids[] = child_fork(
        function ( $cpid ) use ( $name, $size ) { /*  child */
          $store = new IPCShmKeyStore($name, $size);
          
          foreach (range(1,10) as $idx){
            $store->runWithLock(function( $store)use($idx){
              $store->set(0,($store->get(0) ?? 0)+$idx);
            });
            usleep(rand(100,200));
          }
        });
      $cpids[] = child_fork(
        function ( $cpid ) use ( $name, $size ) { /*  child */
          $store = new IPCShmKeyStore($name, $size);
          foreach (range(1,10) as $idx){
            $store->runWithLock(function( $store)use($idx){
              $store->set(0,($store->get(0) ?? 0)+$idx*1000);
            });
            usleep(rand(100,200));
          }
        });
      
      foreach ($cpids as $pid){
        pcntl_waitpid($pid,$st);
      }
      $store = new IPCShmKeyStore($name, $size);
      $result =$store->all();
      $store->destroy();
      $this->assertEquals(55055,$result[0]);
    }
  }
}