<?php

namespace Tests\Units\KeyValueStore;

use Tests\TestCase;
use Takuya\SysV\IPCShmKeyStore;
use function Takuya\Helpers\str_rand;

class SharedMemoryAsArrayTest extends TestCase {
  
  public function test_shm_key_store_methods() {
    $arr = array_map(fn() => str_rand(10), array_fill(0, 5, null));
    $store = new IPCShmKeyStore(str_rand(), 500);
    $ret[] = $store->store($arr);
    $ret[] = $store->all();
    $ret[] = $store->has(0);
    $ret[] = $store->get(0);
    $ret[] = $store->set(0, $msg = '::'.str_rand());
    $ret[] = $store->get(0);
    $ret[] = $store->size();
    $ret[] = $store->del(0);
    $ret[] = $store->size();
    $ret[] = $store->clear();
    $ret[] = $store->all();
    //
    $store->destroy();
    $this->assertEquals([true, $arr, true, $arr[0], true, $msg, sizeof($arr), true, true, sizeof($arr) - 1, []], $ret);
  }
  
  public function test_array_loop_access_to_shm_key_store() {
    $arr = array_map(fn() => str_rand(10), array_fill(0, 5, null));
    $store = new IPCShmKeyStore(str_rand(), 500);
    $store->store($arr);
    foreach ($store as $k => $v) {
      $this->assertEquals($arr[$k], $v);
    }
    $store->destroy();
  }
  
  public function test_array_key_access_to_shm_key_store() {
    $store = new IPCShmKeyStore(str_rand(), 500);
    $store['name'] = ( $msg = str_rand() );
    $this->assertEquals($msg, $store['name']);
    $this->assertEquals(true, ! empty($store['name']));
    $this->assertEquals(true, isset($store['name']));
    unset($store['name']);
    $this->assertEquals(false, ! empty($store['name']));
    $this->assertEquals(false, isset($store['name']));
    $store->destroy();
  }
  public function test_array_key_access_which_no_exists_key() {
    $store = new IPCShmKeyStore(str_rand(), 500);
    $ret[] = $store->get(rand());
    $ret[] = $store[rand()];
    $store->destroy();
    $this->assertEquals([null,null],$ret);
  }
  
  public function test_array_append_to_shm_key_store() {
    $arr = array_map(fn() => str_rand(10), array_fill(0, 5, null));
    $store = new IPCShmKeyStore(str_rand(), 500);
    $store[] = ( $msg = str_rand() );
    $ret = $store->all();
    $store->destroy();
    $this->assertEquals([$msg], $ret);
  }
}