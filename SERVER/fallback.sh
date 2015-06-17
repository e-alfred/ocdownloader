#!/bin/bash
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
$(which nohup) $(which php) $DIR/fallback.php "$1" "$2" "$3" > /tmp/nohup.out 2>&1 &