# changes
### pool.d/www.conf
* listen.mode = 0666
  - default: 0660
* pm = static
  - default: dynamic
* pm.max_children = 16
  - default: 5
* pm.max_requests = 0
  - default: 500
* rlimit_files = 65535
  - default: 1024
* session
  - 最下部にある(file/memcached/memcached(unix socket))

### php.ini
* date.timezone = Asia/Tokyo
  - default: nodefined

### php-fpm.ini
* rlimit_files = 65535
  - default: 1024
* events.mechanism = epoll
  - default: auto detection(poll?)
