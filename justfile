_default:
    just --list

build:
    # TODO: create a Nix derivation and add it to the Nix store instead of copying the phar to my Home directory.
    ./vendor/bin/box compile
    cp dist/versa ~/versa
