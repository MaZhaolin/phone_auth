#!/bin/bash
uglifyjs set.js -c -m -o set.min.js
echo 'set.js => set.min.js'
uglifyjs mobile.js -c -m -o mobile.min.js
echo 'mobile.js => mobile.min.js'
uglifyjs v-helper.js -c -m -o v-helper.min.js
echo 'v-helper.js => v-helper.min.js'
