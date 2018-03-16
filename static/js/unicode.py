import sys

def toUnicode(input, output = False):
  if not output:
    output = input
  fd = open(input, 'r')
  s = fd.read()
  s = s.replace('\n', '')
  s = s.encode('unicode_escape').decode('ascii')
  # s = s.replace('\\n', '\n')
  f = open(output, 'w')
  f.write(s)
  f.close()
  fd.close()

if len(sys.argv) == 2:
  toUnicode(sys.argv[1])
elif len(sys.argv) == 3:
  toUnicode(sys.argv[1], sys.argv[2])
