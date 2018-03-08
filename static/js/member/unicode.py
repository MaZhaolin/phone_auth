import os, pdb

def toUnicode(filepath, filename):
    fd = open(filepath, 'r')
    s = fd.read()
    s = s.encode('unicode_escape')
    s = s.decode('utf-8')
    s = s.replace('\\n', '\n')
    f = open('./' + filename, 'w')
    f.write(s)
    f.close()
    fd.close()
toUnicode('app.91968e99d1fc40d75e26.js', 'app.91968e99d1fc40d75e26.js')
# for (root, dirs, files) in os.walk("./"):
#     for filename in files:
#         toUnicode(os.path.join(root, filename), filename)