<?php

namespace Takuya\SysV;

use RuntimeException;
use SysvSharedMemory;

class IPCSharedMem {
  
  protected int              $ipc_key;
  protected SysvSharedMemory $shm;
  
  /**
   * @param string $name unique name
   * @param int    $size shared memory size to allocate.
   * @param int    $perm IPC permission default is 0770
   */
  public function __construct( public string $name, public int $size = 1024*2, public int $perm = 0770 ) {
    $this->attach();
  }
  
  public static function str_to_key( string $str ):int {
    return IPCSemaphore::str_to_key($str);
  }
  
  /**
   * @return bool
   */
  public function attach():bool {
    $ret = shm_attach($this->key(), $this->size, $this->perm);
    if( ! $ret ) {
      throw new RuntimeException('shm_attach() failed.');
    }
    $this->shm = $ret;
    
    return true;
  }
  
  protected function key():int {
    return $this->ipc_key ??= static::str_to_key($this->name);
  }
  
  /**
   * This fill ZERO to memory and clearing existing data. not shared memory itself.
   * @param int|null $key default $this->ipc_key.
   * @return bool
   */
  public function erase( int $key = null ):bool {
    return ! $this->isEmpty() && shm_remove_var($this->shm, $key ?? $this->ipc_key);
  }
  
  /**
   * @param int|null $key default $this->ipc_key.
   * @return bool
   */
  public function isEmpty( int $key = null ):bool {
    return ! shm_has_var($this->shm, $key ?? $this->ipc_key);
  }
  
  /**
   * return $var that is **un-serialized** automatically.
   * @param int|null $key default $this->ipc_key.
   * @return mixed
   */
  public function get( int $key = null ):mixed {
    return ! $this->isEmpty() ? shm_get_var($this->shm, $key ?? $this->ipc_key) : null;
  }
  
  /**
   * $var will be serialized automatically.
   * @param          $var
   * @param int|null $key default $this->ipc_key.
   * @return bool
   */
  public function put( $var, int $key = null ):bool {
    return shm_put_var($this->shm, $key ?? $this->ipc_key, $var);
  }
  
  /**
   * This demolishes the IPC Segment, not clear data. IPC shared memory entry will be removed.
   * @return bool
   */
  public function destroy():bool {
    return shm_remove($this->shm);
  }
  
  /**
   * @return bool
   */
  public function detach():bool {
    $ret = shm_detach($this->shm);
    unset($this->shm);
    
    return $ret;
  }
}