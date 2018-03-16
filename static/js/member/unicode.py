import os, pdb
import sys

def toUnicode(filepath, filename):
    fd = open(filepath, 'r')
    s = fd.read()
    s = s.replace('\n', '')
    s = s.encode('unicode_escape').decode('ascii')
    # s = s.replace('\\n', '\n')
    f = open('./' + filename, 'w')
    f.write(s)
    f.close()
    fd.close()
toUnicode('../set.min.js', 'set.min.js')
# for (root, dirs, files) in os.walk("./"):
#     for filename in files:
#         toUnicode(os.path.join(root, filename), filename)