#!/bin/bash

# https://github.com/Homebrew/homebrew-core/blob/master/Formula/lib/libxdiff.rb

# Potentially install with https://github.com/FriendsOfPHP/pickle

wget http://www.xmailserver.org/libxdiff-0.23.tar.gz
tar -xvzf libxdiff-0.23.tar.gz
cd libxdiff-0.23
./configure # --disable-debug --disable-dependency-tracking
make
sudo make install
rm -rf libxdiff-0.23.tar.gz libxdiff-0.23
sudo pecl install xdiff
