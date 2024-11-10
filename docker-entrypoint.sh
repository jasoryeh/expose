#!/bin/bash

if [ -z "$EXPOSE_CONFIG_PATH" ]; then
    echo "Unspecified 'exposeConfigPath' setting"
    exit 1
fi

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
