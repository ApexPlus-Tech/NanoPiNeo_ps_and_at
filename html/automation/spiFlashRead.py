import serial
import os
import sys
import json
from time import sleep

def hexPrint(x):
	print(" ".join(hex(ord(n)) for n in x ))

if(len(sys.argv) != 4):
	print("Incorrect arguments")
	sys.exit(-1);
startFreq = int(sys.argv[1])
stopFreq  = int(sys.argv[2])
file_path = sys.argv[3]
file_pointer = open("/var/www/automation/flash_progress.json","w")
file_pointer.close()
ser = serial.Serial('/dev/ttyS1', baudrate=115200, stopbits=serial.STOPBITS_ONE,bytesize=serial.EIGHTBITS,timeout=2)
start = 0
end = 264
write_fd = open("dummyRead.txt",'r+')
dummyData = write_fd.readlines()
dummyData = [x.replace("\r\n","") for x in dummyData]
dummyData = [int(x,16) for x in dummyData]
dummyData = [chr(x) for x in dummyData]
sendData = "".join(dummyData[start:end])
print "length"
print len(sendData)
for freq in range(startFreq,stopFreq+1):
	startAddr = freq*0x4000
	endAddr = (freq+1)*0x4000
	#print(endAddr)
	fileName = file_path+"ReadFiles/file_" + str(freq+1) + ".txt"
	fd = open(fileName, 'w+')
	while startAddr < endAddr :
		print('Address :' + str(hex(startAddr)))
		sendCommand = "rm " + str(hex(startAddr))+" "+str(sendData)+"--EOS--\r"
		ser.flushInput()
		ser.flushOutput()
		ser.write(sendCommand)
		resp = ser.read(264)
		#hexPrint(resp)
		resp = "\r\n".join(str(hex(ord(n))[2:]).zfill(2) for n in resp)
		fd.write(resp)
		fd.write('\r\n')
		startAddr = startAddr + 0x200
		data ={}
		data['progress']=round((freq*1.0/(stopFreq-startFreq+1)) * 99)
		data['finished']="false"
		data['type']="Flash reading"
		print(data['progress'])
		file_pointer = open("/var/www/automation/flash_progress.json","w")
		json.dump(data,file_pointer)
		file_pointer.close()
	fd.close()

write_fd.close()
data={}
data['progress']=100
data['finished']="true"
data['type']="Flash reading"
print(data['progress'])
file_pointer = open("/var/www/automation/flash_progress.json","w")
json.dump(data,file_pointer)
file_pointer.close()

