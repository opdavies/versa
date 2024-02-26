_default:
    just --list

build:
    # TODO: create a Nix derivation and add it to the Nix store instead of copying the phar to my Home directory.
    composer dump-env prod
    ./bin/console cache:clear
    ./bin/console cache:warmup
    ./vendor/bin/box compile
    rm .env.local.php
    cp dist/versa ~/versa
