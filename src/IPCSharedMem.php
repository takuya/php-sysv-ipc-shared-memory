<?php

namespace Takuya\SysV;


class IPCSharedMem {
  
  protected int               $ipc_key;
  protected \SysvSharedMemory $shm;
  
  protected function key():int {
    if( empty($this->ipc_key) ) {
      $seed = crc32($this->name);
      mt_srand($seed);
      $fixed_random_unsigned_int32_seed_by_name = mt_rand(0, PHP_INT_MAX)&0x7FFFFFFF;
      mt_srand(time());
      $this->ipc_key = $fixed_random_unsigned_int32_seed_by_name;
    }
    
    return $this->ipc_key;
  }
  
  /**
   * @param string $name unique name
   * @param int    $size shared memory size to allocate.
   * @param int    $perm IPC permission default is 0770
   */
  
  public function __construct( public string $name, public int $size = 1024*2, int $perm = 0770 ) {
    if( ! ( $this->shm = shm_attach($this->key(), $size, $perm) ) ) {
      throw new \RuntimeException('shm_attach failed.');
    }
  }
  
  public function isEmpty():bool {
    return ! shm_has_var($this->shm, $this->ipc_key);
  }
  
  /**
   * This fill ZERO to memory and clearing existing data. not shared memory itself.
   * @return bool
   */
  public function erase():bool {
    return ! $this->isEmpty() && shm_remove_var($this->shm, $this->ipc_key);
  }
  
  /**
   * return $var that is **unserialized** automatically.
   * @return mixed
   */
  
  public function get():mixed {
    return shm_get_var($this->shm, $this->ipc_key);
  }
  
  /**
   * $var will be serialized automatically.
   * @param $var
   * @return bool
   */
  public function put( $var ):bool {
    return shm_put_var($this->shm, $this->ipc_key, $var);
  }
  
  /**
   * This demolishes the IPC Segment, not clear data. IPC shared memory entry will be removed.
   * @return bool
   */
  
  public function destroy():bool {
    return shm_remove($this->shm);
  }
}