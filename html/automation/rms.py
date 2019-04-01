import sys
import math
filename=sys.argv[1];
#flag=sys.argv[2];
fp=open(filename,'r')
POINTS=5
#fpOut=open(filename,'w')
#if flag=='a' or flag=='p':
#Do attenuator measurements
rms=[[] for x in range(POINTS)]
#print rms 
lines=fp.readlines()
nameplate=lines[0]
#print "Nameplate :" +nameplate
#print lines
lines=lines[1:]
#convert list of float to string
ind=0
for line in lines:
	line=line.split("\t")
	value=line[0]
	line=line[1:]
	index=0
	for num in line:
		v=float(num)
		line[index]=v-float(value)
		rms[index].append(line[index])
		index=index+1
	#print line
	ind+=1
iter=0
for array in rms:
	newArray=[x*x for x in array]
	length=len(newArray)
	#print "NEW ARRAY"
	#print  newArray
	rmsValue=math.sqrt(sum(newArray)/length)
	rms[iter]=rmsValue
	iter=iter+1
lastString='\t'.join(str(x) for x in rms)
lastString="Rms Error\t"+lastString
#print lastString
fp.close()
fp=open(filename,'a')
fp.write(lastString)
fp.close()
