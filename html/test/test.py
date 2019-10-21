from time import sleep
import os
cnt = 1
#clear the progress file
fp = open("progress.txt","w")
fp.close()
for cnt in range(0,100):
	print(str(cnt))
	fp = open("progress.txt","a")
	fp.write(str(cnt)+"\n")
	fp.close()
	try:
		cnt = cnt + 1
		sleep(1)
	except:
		print("Exception occured")

