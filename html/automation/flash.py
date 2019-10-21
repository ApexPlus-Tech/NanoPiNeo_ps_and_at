import serial
from time import sleep
import json

def hexPrint(x):
	print(" ".join(hex(ord(n)) for n in x))#to print in hexadecimal

ser = serial.Serial('/dev/ttyS1',baudrate=115200, stopbits=serial.STOPBITS_ONE,bytesize=serial.EIGHTBITS,timeout=2)
#clear the progress file 
file_pointer = open("/var/www/automation/flash_progress.json","w")
file_pointer.close()
for freq in range(160):
	fp = open("/var/www/automation/session.txt")
	PathName=fp.readline()
	PathName=PathName.strip("\r")
	PathName=PathName.strip("\n")
	fp.close()
	start = 0
	end =264
	startAddr = freq*0x4000
	endAddr = (freq+1)*0x4000
	fileName = "/var/www/automation/boards/"+PathName+"/Files/file_" + str(freq+1) + ".txt"
	print(fileName)
	fd = open(fileName, 'r+')
	data = fd.readlines()
	try:
		data = [x.replace("\r\n","") for x in data]
		data = [int(x,16) for x in data]
		data = [chr(x) for x in data]
	except Exception as e:
		fi=open("logger_py.txt","w")
		string=" ".join(str(i) for i in data)
		fi.write(string)
		fi.close()
		print(data)
		print(str(e))
	#data = [ord(x) for x in data]
	#print(" ".join(data[0:264*1]))
	#print(" ".join(data[26*1:26*2]))
	print('Frequency ID :' + str(freq))
	while startAddr < endAddr :
		#print(hex(startAddr))
		#startAddr = startAddr+0x0200
		print('Address : ' + str(hex(startAddr)))
		sendData = "".join(data[start:end])
		#sendData = sendData.replace("\r","\\r")
		sendCommand = "wm " + str(hex(startAddr))+" " + str(sendData)+"--EOS--\r"
		#sendData = [ord(x) for x in sendData]
		#hexPrint(sendData)
		ser.write(sendCommand)
		resp = ser.read(264)
		#print(resp)
		#hexPrint(resp)
		while list(sendData) != list(resp):
			#hexPrint(sendData)
			ser.write(sendCommand)
			resp = ser.read(264)
			#print('2nd Receive :')
			#hexPrint(resp)

		if list(sendData)== list(resp):
			print('SUCCESS')
		else:
			print('FAIL')

		startAddr = startAddr + 0x0200
		start = end
		end = end + 264
		#print(resp)
		#sleep(0.05)
	jsonData={}
	jsonData['progress'] = round((freq/160.0) * 99)
	jsonData['type']     = "Flash writing"
	jsonData['finished'] = "false"
	with open("/var/www/automation/flash_progress.json","w") as filePointer:
		json.dump(jsonData,filePointer)
jsonData={}
jsonData['progress'] = 100
jsonData['type']     = "Flash writing"
jsonData['finished'] = "true"
with open("/var/www/automation/flash_progress.json","w") as filePointer:
	json.dump(jsonData,filePointer)

