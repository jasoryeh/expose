#!/bin/bash

if [ -z "$DOMAIN" ]; then
    echo "Unspecified 'domain' setting"
    exit 1
fi

if [ -z "$PORT" ]; then
    echo "Unspecified 'port' setting"
    exit 1
fi

if [[ $# -eq 0 ]]; then
    exec /src/expose serve ${DOMAIN} --port ${PORT} --validateAuthTokens
else
    exec /src/expose "$@"
fi
