#!/usr/bin/env bash

# Install pre commit hooks.
cp ./tools/pre-commit ./.git/hooks/pre-commit
cp ./tools/commit-msg ./.git/hooks/commit-msg
chmod +x ./.git/hooks/pre-commit
chmod +x ./.git/hooks/commit-msg

# Install the Symfony coding standard.
pushd ./vendor/squizlabs/php_codesniffer/CodeSniffer/Standards
rm -rf Symfony2
git clone https://github.com/ebidtech/Symfony2-coding-standard.git Symfony2
popd
