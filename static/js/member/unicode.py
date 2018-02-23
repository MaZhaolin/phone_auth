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

for (root, dirs, files) in os.walk("/Users/insertsweat/Documents/workspace/discuz/source/plugin/phone_auth/static/app/dist/static/js/"):
    for filename in files:
        toUnicode(os.path.join(root, filename), filename)