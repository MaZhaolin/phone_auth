#!/bin/bash

uglifyjs "$1.js" -c -m -o "../js/$1.min.js"
python3 unicode.py "../js/$1.min.js"
echo "$1.js => $1.min.js"

