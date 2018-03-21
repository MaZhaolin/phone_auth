#!/bin/bash

uglifyjs "$1.js" -c -m -o "$1.min.js"
python3 unicode.py "$1.min.js"
echo "$1.js => $1.min.js"

