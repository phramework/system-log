#!/bin/bash

PHRAMEWORK_SYSTEM_LOG_PORT=8091
echo "Starting web server at http://localhost:$PHRAMEWORK_SYSTEM_LOG_PORT...";
(cd "$(dirname $BASH_SOURCE)/../tests/APP/"; php -S localhost:$PHRAMEWORK_SYSTEM_LOG_PORT)
