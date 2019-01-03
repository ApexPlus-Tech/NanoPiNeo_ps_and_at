#!/usr/bin/python
import json
print "Content-type:application/json\n\n"
result={'voltage':'1.7','current':'4.5'}
print json.dumps(result)
