#!/usr/bin/python
import json
print "Content-type:application/json\n\n"
result={'voltage':'1.4','current':'4.5'}
print json.dumps(result)
