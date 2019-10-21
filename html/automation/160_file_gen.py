
# By Amit Kumar
# date: 6/13/2019


import even_odd                                                    # imported odd_even file for easy odd-even check
import os
import subprocess
import math
import json 

fd = open("/var/www/automation/flash_progress.json","w")
fd.close()
def file_len(fname):
    p = subprocess.Popen(['wc', '-l', fname], stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    result, err = p.communicate()
    if p.returncode != 0:
        raise IOError(err)
    return int(result.strip().split()[0])

def main(current_phase_file):                                      # takes file name from main call
    ff = open(Phase_path + current_phase_file, 'r')
    index = 1                                                      # (each value corresponds to a output file)
    m_index = 1
    flag = True                                                    # created for leaving the 1st row of Phase file

    for lines in ff:
        if flag:
            flag = False
            continue                                               # skipping first line
        else:
            split = lines.split()
            if len(split) == 161:
                split = split[1:]                                  # leaving first value {attenuation value}(not needed)
            else:
                split = split

            for words in split:
                var_f = Phase_path+'Files/file_'+ str(index) + '.txt'              # generates 160 file names
                with open(var_f, "a") as g:                        # opens each file and write to it
                    if even_odd.check(m_index) == 'odd':           # if odd, means phase row is going on
                        value = (((float(words)+180)/360)*255)     # convert to byte
                        temp = float("{0:.2f}".format(value))      # float up to 2 decimal place
                        temp_1 = format(int(math.floor(temp)), 'x')          # round in to convert into hex
                        if int(temp_1,16) >= 256 or int(temp_1,16) < 0:
                            print("PHASE ERROR")
                            print(words)
                        if len(temp_1) == 1:                       # Mr HARSHA wants byte length equal = 2 like 0f,ff,01
                            temp_1 = '0' + temp_1
                            g.write(str(temp_1))
                        else:
                            g.write(str(temp_1))
                        g.write('\n')
                    else:                                          # even , attenuation row is going on
                        value = (((float(words)+15)/47)*255)        # byte conversion
                        temp = float("{0:.2f}".format(value))
                        temp_1 = format(int(math.floor(temp)), 'x')          # same process as it was for even
                        if int(temp_1,16) >= 256 or int(temp_1,16)<0:
                            print("ATTENTUATION ERROR")
                            print(words)
                        if len(temp_1) == 1:
                            temp_1 = '0' + temp_1
                            g.write(str(temp_1))
                        else:
                            g.write(str(temp_1))
                        g.write('\n')

                index += 1
            index = 1                                              # index again initiated to 1 for next row
            m_index += 1                                            # increment with each row, for even odd check

    g.close()                                                      # close write file
    ff.close()                                                     # close read file


if __name__ == '__main__':                                         # start program from here
    fp = open("/var/www/automation/session.txt")
    PathName=fp.readline();
    PathName=PathName.strip("\r")
    PathName=PathName.strip("\n")
    fp.close()
    Phase_path = '/var/www/automation/boards/'+PathName+'/'    # location of input phase files
    os.system("rm -r --interactive=never "+Phase_path+"Files")
    os.system("mkdir "+Phase_path+"Files")
    for i in range(0, 64):                                         # run main() for 64 times to read from 64 diff files
	try:
            '''data = {}
	    data['progress']=round((i*1.0/64) * 99);
            data['finished']="false"
            data['type']="160 files generation"
            print(data['progress'])
            open_file = 'Phase' + str(i) + '.txt'                    # phase file name {input file}
            fd    = open("/var/www/automation/flash_progress.json","w")
            json.dump(data,fd)
            #fd.write(str(i)+"\n")
            fd.close()'''
            open_file = 'Phase' + str(i) + '.txt'                    # phase file name {input file}
            f = open(Phase_path + open_file, 'r')                      # open input file from the path, check for rows
            rows = len(f.readlines())
            f.close()                                                  # close
            f = open(Phase_path + open_file, 'r')                      # open again the same file to check for cols
            first_line = f.readline()
            cols = len(first_line.split())
            f.close()
            if rows == 129 and cols == 160:                            # if file has expected row and cols then call main()
                main(open_file)
		data = {}
                data['progress']=round((i*1.0/64) * 99);
                data['finished']="false"
                data['type']="160 files generation"
                print(data['progress'])
                fd    = open("/var/www/automation/flash_progress.json","w")
                json.dump(data,fd)
                #fd.write(str(i)+"\n")
                fd.close()
            else:
                print("input file format not ok")
            print("read file phase_{}" .format(i+1))                   # status (that much input file finished reading)
        except Exception as e:
            print("SOME ERROR OCCURED")
            print(str(e))

    for j in range(0,160):
	fileName=Phase_path+"Files/file_"+str(j+1)+".txt"
        fileLen=file_len(fileName)
	print("File Name:"+fileName+",File length:"+str(fileLen))
        with open(fileName,"a") as fp:
            while fileLen<8448:
                fp.write("00\n")
                fileLen=fileLen+1
        print("File Name:"+fileName+",File length:"+str(fileLen))
    data = {}
    data['progress']=100;
    data['finished']="true"
    data['type']    ="160 files generation"
    print(data['progress'])
    fd    = open("/var/www/automation/flash_progress.json","w")
    json.dump(data,fd)
    fd.close()
