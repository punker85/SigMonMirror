import sys
import time
from datetime import datetime
import controller
import device
import filtering
import pymysql

#Nov20-0121 - has Phone Test
#Nov20-0452 - has Phone Test 9M
input_file = '/home/pi/btmon_output/gps-Nov20-1302.txt'
#input_file = 'algorithm_test.txt'
#test_mac = '00:5B:94:28:C3:EE'
program_starttime = None
pi_number = 0 #corresponds to Kass pi = 2


def parse_file(filename):
    with open(filename) as f:
        content = f.readlines()
    return content
    

def parse_entry(device_list):
    samples = []
    for x in device_list:
    
        bt_device = device.Device()

        x_list = x.split(' | ')
        mac = x_list[0]
        rssi = x_list[1]
        timestamp = x_list[2]
        date = '-'.join([timestamp[20:24], timestamp[4:7], timestamp[8:10]])
        date = date + ' ' + timestamp[11:19]
        datetimeObj = datetime.strptime(date, '%Y-%b-%d %H:%M:%S')
        
        bt_device.set_mac(mac)
        bt_device.set_rssi(int(rssi))
        bt_device.set_scan_date(str(datetimeObj))
        
        bt_device_str = bt_device.scan_date + " | " + bt_device.mac + " | " + str(bt_device.distance)
        
        #if(bt_device.mac == test_mac):
            #print(bt_device.scan_date + " | " + bt_device.mac + " | " + str(bt_device.rssi) + " | Distance: " + str(bt_device.distance))       

        samples.append(bt_device_str)
        
    return samples


def get_experiment_num_from_db(scanner, str_time_start):
    cred_lines = []
    results = None
    with open ('DB_Credentials.txt', 'rt') as cred_file:
        for line in cred_file:
            cred_lines.append(line)       
    try:
        db = pymysql.connect(host=cred_lines[0][:-1],
                             port=int(cred_lines[1][:-1]),
                             user=cred_lines[2][:-1],
                             password=cred_lines[3][:-1],
                             db=cred_lines[4][:-1])
        #print("Connected")
    except pymysql.Error as e:
        print("Error {0}: {1}".format(e.args[0], e.args[1]))
        sys.exit(1)
        
    cur = db.cursor()
    time_start = datetime.strptime(str_time_start, '%Y-%m-%d %H:%M:%S')
    sql = "SELECT id FROM experiment WHERE scanner = {0} AND time_start = '{1}'".format(scanner, str_time_start)
    #sql = "SELECT * FROM experiment"
    try:
        
        cur.execute(sql)
        print("Success")
        results = list(cur.fetchall())
    except:
        print("Error: unable to fetch data")
        
    cur.close()
    for col in results:
        experiment_num = col[0]
    return experiment_num


def insert_into_distance_db(distance_entry):
    cred_lines = []
    results = None
    with open ('DB_Credentials.txt', 'rt') as cred_file:
        for line in cred_file:
            cred_lines.append(line)       
    try:
        db = pymysql.connect(host=cred_lines[0][:-1],
                             port=int(cred_lines[1][:-1]),
                             user=cred_lines[2][:-1],
                             password=cred_lines[3][:-1],
                             db=cred_lines[4][:-1])
        #print("Connected")
    except pymysql.Error as e:
        print("Error {0}: {1}".format(e.args[0], e.args[1]))
        sys.exit(1)
        
    cur = db.cursor()
    sql = "INSERT INTO dist (experiment, mac, start_time, distance) VALUES ({0}, '{1}', '{2}', {3})".format(distance_entry[3], distance_entry[0], distance_entry[2], distance_entry[1])
    print(sql)
    
    try:
        
        cur.execute(sql)
        print("Success")
        print("Primary key id of the last inserted row:")
        print(cursorObject.lastrowid)
        print(mycursor.rowcount, "record inserted.")
        
    except:
        print("Error: unable to fetch data")
        
    cur.close()
        
if __name__ == '__main__':
    file_content = parse_file(input_file)
    device_list = file_content[3:]
    time = file_content[0]
    program_starttime = time[5:]
    print(program_starttime)

    experiment_num = get_experiment_num_from_db(pi_number, program_starttime)
    
    samples = parse_entry(device_list)
    groups = filtering.group_samples(samples)
    distance_list = filtering.kalman_filtering(groups) #has the mac, distance, start time
    
    for distance in distance_list:
        distance.append(experiment_num)
        insert_into_distance_db(distance)       

