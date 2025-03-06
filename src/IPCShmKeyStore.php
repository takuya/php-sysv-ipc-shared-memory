<?php

namespace Takuya\SysV;

class IPCShmKeyStore implements \ArrayAccess, \Countable, \IteratorAggregate {
  
  protected IPCSharedMem $shm;
  protected IPCSemaphore $sem;
  
  /**
   * @param string $name unique name
   * @param int    $size shared memory size to allocate.
   * @param int    $perm IPC permission default is 0770
   */
  public function __construct( public string $name, public int $size = 1024*10, public int $perm = 0770 ) {
    $this->shm = new IPCSharedMem($this->name, $this->size, $this->perm);
    $this->sem = new IPCSemaphore($this->name.'_sem', $this->perm);
  }
  
  protected function withLock( callable $fn ) {
    return $this->sem->withLock($fn);
  }
  protected function load():array{
      return $this->shm->get() ?? [];
  }
  
  public function destroy():bool {
    return $this->sem->acquire()
           && $this->shm->destroy()
           && $this->sem->destroy();
  }
  
  public function size():int {
    return sizeof($this->all());
  }
  
  public function isEmpty():bool {
    return empty($this->all());
  }
  
  ////////////////////////////////////////
  /// --- Basic CRUD methods
  ////////////////////////////////////////
  public function all():array {
    return $this->withLock(fn() => $this->load());
  }
  
  public function clear():bool {
    return $this->withLock(fn() => $this->shm->erase());
  }
  
  public function store( array $items ):bool {
    return $this->withLock(fn() => $this->shm->put($items));
  }
  
  public function del( string|int $key ):bool {
    return $this->withLock(function () use ( $key ):bool {
      $items = $this->load();
      unset($items[$key]);
      
      return $this->shm->put($items);
    });
  }
  
  public function set( string|int|null $key, mixed $val ):bool {
    return $this->withLock(function () use ( $key, $val ):bool {
      $items = $this->load();
      is_null($key) ? $items[] = $val : $items[$key] = $val;
      
      return $this->shm->put($items);
    });
  }
  
  public function get( string|int $key ):mixed {
    return $this->withLock(fn() => $this->load()[$key] ?? null);
  }
  
  public function has( string|int $key ):bool {
    return $this->withLock(fn() => isset($this->load()[$key]));
  }
  
  public function add( mixed $val ):bool {
    return $this->set(null, $val);
  }
  ///////////////////////////////////////////////////
  //-- Interface implementations.
  ///////////////////////////////////////////////////
  public function offsetExists( mixed $offset ):bool {
    return $this->has($offset);
  }
  
  public function offsetGet( mixed $offset ):mixed {
    return $this->get($offset);
  }
  
  public function offsetSet( mixed $offset, mixed $value ):void {
    $this->set($offset, $value);
  }
  
  public function offsetUnset( mixed $offset ):void {
    $this->del($offset);
  }
  
  public function getIterator():\Traversable {
    return new \ArrayIterator($this->all());
  }
  
  public function count():int {
    return $this->size();
  }
}