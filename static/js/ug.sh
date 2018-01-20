#!/bin/bash
uglifyjs set.js -c -m -o set.min.js
uglifyjs mobile.js -c -m -o mobile.min.js
uglifyjs v-helper.js -c -m -o v-helper.min.js
