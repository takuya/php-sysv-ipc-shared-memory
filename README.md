# php-sysv-ipc-shared-memory 

This package is wrapper for php sysv shm_xxx.
## Installing 
from Packagist 
```shell

```
from GitHub
```shell
name='php-sysv-ipc-shared-memory'
composer config repositories.$name \
vcs https://github.com/takuya/$name  
composer require takuya/$name:master
composer install
```
### comparison to shm_open

Compare to shared memory function (`shm_open`) , one big advantage in SysV.

Sysv function (ex `shm_put_var`) has auto serialization.

### remove ipc by manually 

If unused ipc remains. use SHELL command to remove.

```shell
ipcs -m | grep $USER | grep -oE '0x[a-f0-9]+' | xargs -I@ ipcrm --shmem-key @
```




