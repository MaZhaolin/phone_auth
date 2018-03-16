#!/bin/bash
uglifyjs set.js -c -m -o set.min.js
python3 unicode.py set.min.js
echo 'set.js => set.min.js'

uglifyjs mobile.js -c -m -o mobile.min.js
python3 unicode.py mobile.min.js
echo 'mobile.js => mobile.min.js'

uglifyjs v-helper.js -c -m -o v-helper.min.js
python3 unicode.py v-helper.min.js
echo 'v-helper.js => v-helper.min.js'

