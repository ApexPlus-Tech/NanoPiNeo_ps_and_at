import serial
import sys
import time 

ser=serial.Serial("/dev/ttyS1",115200,timeout=1)
TIMEOUT=1
startTime=time.time()
ser.write("\r")
while (time.time() - startTime < TIMEOUT):
	if ser.read(1) == 'O':
		if(ser.read(1) == 'K'):
			print "Y"
			sys.exit(0)
print "N"
