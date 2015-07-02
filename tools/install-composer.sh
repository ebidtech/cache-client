#!/bin/bash

# Set the composer version to install.
COMPOSER_VERSION=1.0.0-alpha10

# Download composer.
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
if [ -z ${COMPOSER_VERSION+x} ]; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir="$DIR/../"
else
    curl -sS https://getcomposer.org/installer | php -- --install-dir="$DIR/../" --version="$COMPOSER_VERSION"
fi
